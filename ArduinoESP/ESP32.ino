#include <Wire.h>
#include <WiFi.h>
#include <WebServer.h>
#include <MAX30100_PulseOximeter.h>

#define RXD2 16
#define TXD2 17

HardwareSerial mySerial(1);             
PulseOximeter pox;                      

const char* ssid = "HGW-F80FE1";
const char* password = "Yanga_12";   

WebServer server(80);                   

float bodyTemp = 0.0;
float ecg = 0.0;
float pulseRate = 0.0;
float spo2 = 0.0;

unsigned long lastValidBodyTempTime = 0;
unsigned long lastValidEcgTime = 0;
unsigned long lastValidPulseTime = 0;
unsigned long lastValidSpO2Time = 0;
const unsigned long timeoutDuration = 8000;

unsigned long lastPoxUpdateTime = 0;


void handleRoot() {
  String html = "<html><head><meta http-equiv='refresh' content='2'/></head><body>";
  html += "<h1>Live Health Monitoring</h1>";
  html += "<p><b>Body Temp:</b> <span id='bodyTemp'>" + String(bodyTemp, 2) + " °C</span></p>";
  html += "<p><b>ECG:</b> <span id='ecg'>" + String(ecg, 1) + " BPM</span></p>";
  html += "<p><b>Pulse Rate:</b> <span id='pulseRate'>" + String(pulseRate, 1) + " BPM</span></p>";
  html += "<p><b>SpO2:</b> <span id='spo2'>" + String(spo2, 1) + " %</span></p>";

  html += "<script>";
  html += "function updateValues() {";
  html += "  fetch('/data')";
  html += "    .then(response => {";
  html += "      if (!response.ok) { throw new Error('Network response was not ok'); }";
  html += "      return response.text();";
  html += "    })";
  html += "    .then(data => {";
  html += "      try {";
  html += "        let values = data.split(',');";
  html += "        if (values.length === 4) {";
  html += "          document.getElementById('bodyTemp').innerText = values[0] + ' °C';";
  html += "          document.getElementById('ecg').innerText = values[1] + ' BPM';";
  html += "          document.getElementById('pulseRate').innerText = values[2] + ' BPM';";
  html += "          document.getElementById('spo2').innerText = values[3] + ' %';";
  html += "        } else {";
  html += "          console.error('Invalid data format received');";
  html += "        }";
  html += "      } catch (error) {";
  html += "        console.error('Error parsing data:', error);";
  html += "      }";
  html += "    })";
  html += "    .catch(error => {";
  html += "      console.error('Error fetching data:', error);";
  html += "    });";
  html += "}";
  html += "setInterval(updateValues, 1000);";
  html += "</script>";

  html += "</body></html>";
  server.send(200, "text/html", html);
}

void handleData() {
  String temp = isnan(bodyTemp) ? "0.00" : String(bodyTemp, 2);
  String ecgVal = isnan(ecg) ? "0.0" : String(ecg, 1);
  String heartRate = isnan(pulseRate) ? "0.0" : String(pulseRate, 1);
  String oxygen = isnan(spo2) ? "0.0" : String(spo2, 1);

  String data = temp + "," + ecgVal + "," + heartRate + "," + oxygen;
  server.send(200, "text/plain", data);
}

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected! IP: " + WiFi.localIP().toString());
}

void setup() {
  Serial.begin(115200);
  mySerial.begin(115200, SERIAL_8N1, RXD2, TXD2);

  connectWiFi();

  if (!pox.begin()) {
    Serial.println("FAILED to initialize MAX30100!");
  } else {
    Serial.println("MAX30100 initialized.");
    pox.setIRLedCurrent(MAX30100_LED_CURR_11MA);
  }

  server.on("/", handleRoot);
  server.on("/data", handleData);
  server.begin();
}

void loop() {
  // ----- Serial1 ECG + Temp -----
  if (mySerial.available()) {
    String data = mySerial.readStringUntil('\n');
    Serial.print("Received from Arduino: ");
    Serial.println(data);
    
    int comma1 = data.indexOf(',');

    if (comma1 != -1) {
      float newEcg = data.substring(0, comma1).toFloat();
      float newBodyTemp = data.substring(comma1 + 1).toFloat();

      if (newEcg != 0.0) {
        ecg = newEcg;
        lastValidEcgTime = millis();
        Serial.print("Updated ECG: "); Serial.println(ecg);
      }

      if (newBodyTemp != 0.0) {
        bodyTemp = newBodyTemp;
        lastValidBodyTempTime = millis();
        Serial.print("Updated Body Temp: "); Serial.println(bodyTemp);
      }
    } else {
      Serial.println("Invalid data format received");
    }
  }

  // ----- MAX30100 SpO2 + Pulse -----
  pox.update();

  if (millis() - lastPoxUpdateTime >= 100) {
    lastPoxUpdateTime = millis();
    float newPulse = pox.getHeartRate();
    float newSpO2 = pox.getSpO2();

    if (newPulse > 0) {
      pulseRate = newPulse;
      lastValidPulseTime = millis();
    }

    if (newSpO2 > 0) {
      spo2 = newSpO2;
      lastValidSpO2Time = millis();
    }

    Serial.print("Pulse Rate: "); Serial.print(pulseRate);
    Serial.print(" BPM, SpO2: "); Serial.print(spo2); Serial.println(" %");
  }

  // ----- Timeout Reset -----
  if (millis() - lastValidEcgTime > timeoutDuration) ecg = 0.0;
  if (millis() - lastValidBodyTempTime > timeoutDuration) bodyTemp = 0.0;
  if (millis() - lastValidPulseTime > timeoutDuration) pulseRate = 0.0;
  if (millis() - lastValidSpO2Time > timeoutDuration) spo2 = 0.0;

  // ----- Web Handler -----
  server.handleClient();
}