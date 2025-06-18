// Include necessary libraries for WiFi, HTTP requests, SPI, and RFID
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <SoftwareSerial.h>

// Define pins for RFID and buzzer
#define SS_PIN 15               // Slave Select pin for RFID module
#define RST_PIN 2               // Reset pin for RFID module
#define BUZZER_PIN 5            // Buzzer connected to GPIO5 (D1 on NodeMCU)

// Define pins for Arduino communication
#define ARDUINO_RX 4  // D2 on NodeMCU (GPIO4) - Connected to Arduino's TX
#define ARDUINO_TX 0  // D3 on NodeMCU (GPIO0) - Connected to Arduino's RX

MFRC522 mfrc522(SS_PIN, RST_PIN); // Create an instance of the MFRC522 RFID reader

// Replace with your Wi-Fi credentials
const char* ssid = "HGW-F80FE1";      // Wi-Fi SSID (network name)
const char* password = "Yanga_12";    // Wi-Fi password

// Initialize WiFi and HTTP client
WiFiClient client;
HTTPClient http;
SoftwareSerial arduinoSerial(ARDUINO_RX, ARDUINO_TX); // SoftwareSerial for Arduino communication

String lastUID = "";                  // Store the last scanned RFID UID to avoid duplicate scans
String lastBPValue = "";              // Store the last received BP value
unsigned long lastScanTime = 0;       // Timestamp of the last scan
unsigned long lastSendTime = 0;       // Timestamp of the last BP send
const unsigned long scanCooldown = 2000; // Minimum delay (2 seconds) between scans of the same card
const unsigned long sendCooldown = 2000; // Minimum delay (2 seconds) between BP sends

// Function to make the buzzer beep a given number of times
void beep(int count, int duration) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);   // Turn on buzzer
    delay(duration);                  // Keep it on for specified duration
    digitalWrite(BUZZER_PIN, LOW);    // Turn off buzzer
    delay(200);                       // Short delay before next beep
  }
}

void setup() {
  Serial.begin(115200);               // Start serial communication for debugging
  SPI.begin();                        // Initialize SPI communication
  mfrc522.PCD_Init();                 // Initialize RFID reader
  arduinoSerial.begin(115200);        // Start communication with Arduino
  pinMode(BUZZER_PIN, OUTPUT);        // Set buzzer pin as output

  // Start connecting to Wi-Fi
  WiFi.begin(ssid, password);
  int attempts = 0;

  // Try connecting to Wi-Fi up to 10 times
  while (WiFi.status() != WL_CONNECTED && attempts < 10) {
    delay(1000);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nConnected to Wi-Fi");
    beep(1, 1000);                    // One long beep for successful Wi-Fi connection
  } else {
    Serial.println("\nWi-Fi Connection Failed!");
    beep(3, 1000);                    // Three long beeps for failed Wi-Fi connection
  }
}

void handleRFID() {
  // Check if a new RFID card is present and can be read
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    return;                           // If no new card is detected, exit function
  }

  // Construct UID string from the read RFID card
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uid += String(mfrc522.uid.uidByte[i], HEX);  // Convert byte to hex string
  }

  // Avoid reading the same UID again within 2 seconds
  if (uid == lastUID && millis() - lastScanTime < scanCooldown) {
    return;                           // Skip sending the same UID within cooldown
  }

  lastUID = uid;                      // Update last scanned UID
  lastScanTime = millis();           // Update timestamp of the last scan

  Serial.println("Scanning UID: " + uid);  // Print UID to serial monitor

  // Send UID to server using HTTP POST request
  if (WiFi.status() == WL_CONNECTED) {
    http.begin(client, "http://192.168.100.126/StazSys/getUID.php"); //Change to PC IP
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    String postData = "UIDresult=" + uid;
    int httpCode = http.POST(postData);
    String payload = http.getString();

    Serial.println("HTTP Code: " + String(httpCode));
    Serial.println("Response: " + payload);

    http.end();
    beep(1, 200);  // Short beep for successful RFID read
  } else {
    Serial.println("Wi-Fi Disconnected! Skipping RFID request.");
  }
}

void handleBPData() {
  if (arduinoSerial.available()) {
    String data = arduinoSerial.readStringUntil('\n');
    data.trim();
    
    // Debug print received data
    Serial.println("Received from Arduino: " + data);
    
    // Check if it's a buzzer command
    if (data.startsWith("BUZZER:")) {
      // Parse buzzer command
      String params = data.substring(7); // Remove "BUZZER:"
      int commaIndex = params.indexOf(',');
      if (commaIndex != -1) {
        int count = params.substring(0, commaIndex).toInt();
        int duration = params.substring(commaIndex + 1).toInt();
        beep(count, duration);
      }
      return;
    }
    
    // Handle BP data like other sensors
    if (data.startsWith("BP:") && (millis() - lastSendTime >= sendCooldown)) {
      String bpValue = data.substring(3); // Remove "BP:"
      
      if (bpValue != lastBPValue) {
        lastBPValue = bpValue;
        lastSendTime = millis();
        
        // Parse systolic and diastolic values
        int separatorIndex = bpValue.indexOf('/');
        if (separatorIndex != -1) {
          String systolic = bpValue.substring(0, separatorIndex);
          String diastolic = bpValue.substring(separatorIndex + 1);
          
          // Remove any status text from diastolic (e.g., " (High)")
          int statusIndex = diastolic.indexOf(' ');
          if (statusIndex != -1) {
            diastolic = diastolic.substring(0, statusIndex);
          }
          
          if (WiFi.status() == WL_CONNECTED) {
            Serial.println("WiFi Status: Connected");
            Serial.println("Local IP: " + WiFi.localIP().toString());
            
            http.begin(client, "http://192.168.100.126/StazSys/updateBP.php"); //Change to PC's IP
            http.addHeader("Content-Type", "application/x-www-form-urlencoded");
            
            String postData = "systolic=" + systolic + "&diastolic=" + diastolic + "&error_message=";
            Serial.println("POST Data: " + postData);
            
            int httpCode = http.POST(postData);
            String payload = http.getString();
            
            Serial.println("HTTP Code: " + String(httpCode));
            Serial.println("Server Response: " + payload);
            
            if (httpCode == -1) {
              Serial.println("Connection failed. WiFi Status: " + String(WiFi.status()));
              Serial.println("RSSI: " + String(WiFi.RSSI()));
            }
            
            http.end();
          } else {
            Serial.println("WiFi not connected. Status: " + String(WiFi.status()));
          }
        }
      }
    } else if (data == "TEST") {
      // Handle test message
      Serial.println("Received test message from Arduino");
      arduinoSerial.println("TEST_OK");
    }
  }
}

void loop() {
  // Handle RFID reading
  handleRFID();
  
  // Handle BP data
  handleBPData();
  
  // Small delay to prevent overwhelming the serial buffer
  delay(10);
}