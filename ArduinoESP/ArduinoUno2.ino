#include <SoftwareSerial.h>

#define BUTTON_PIN 8
#define IN1 10
#define IN2 11
#define EEP 9
#define ESP_RX 3  // Connected to ESP8266's TX (D3/GPIO0)
#define ESP_TX 2  // Connected to ESP8266's RX (D2/GPIO4)

SoftwareSerial espSerial(ESP_RX, ESP_TX); // SoftwareSerial for ESP8266 communication

// Timing Constants (adjust if needed)
#define START_DELAY 3000      // 3s pre-measurement delay
#define MOTOR_RUN_TIME 8000   // 8s normal measurement time
#define MOTOR_ERROR_TIME 10000 // 10s error case
#define COOLDOWN_TIME 5000    // 5s cooldown
#define ERROR_COOLDOWN 15000  // 15s cooldown after error
#define ERROR_PERSIST_TIME 5000 // 5s to persist error state

// State Tracking
bool processStarted = false;
bool motorRunning = false;
bool inCooldown = false;
bool errorOccurred = false;
bool interrupted = false;
bool errorShown = false;  // Track if error has been shown
bool motorErrorComplete = false; // Track if motor error time is complete
bool inErrorCooldown = false; // Track if we're in error cooldown period
bool errorStatePersisted = false; // Track if error state has been persisted

unsigned long processStartTime = 0;
unsigned long motorStartTime = 0;
unsigned long cooldownStartTime = 0;
unsigned long errorCooldownStartTime = 0; // Track when error cooldown started
unsigned long errorPersistStartTime = 0; // Track when error state started persisting
unsigned long currentMotorRunTime = MOTOR_RUN_TIME;

String bpValue = "N/A"; // Initialize with default

// Function to send buzzer command to ESP8266
void sendBuzzerCommand(int count, int duration) {
  String command = "BUZZER:" + String(count) + "," + String(duration);
  espSerial.println(command);
  delay(50); // Small delay to ensure transmission
}

void setup() {
  Serial.begin(115200);       // Debugging (optional)
  espSerial.begin(115200);    // Must match ESP8266's baud rate!
  
  // Test communication
  Serial.println("Testing ESP8266 communication...");
  espSerial.println("TEST");
  delay(1000);
  
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(IN1, OUTPUT);
  pinMode(IN2, OUTPUT);
  pinMode(EEP, OUTPUT);

  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  digitalWrite(EEP, HIGH); // Enable motor driver
  
  Serial.println("BP Monitor Ready. Press button to start.");
}

void loop() {
  unsigned long currentTime = millis();
  bool buttonPressed = (digitalRead(BUTTON_PIN) == LOW);

  // Check if error cooldown has expired
  if (inErrorCooldown && (currentTime - errorCooldownStartTime >= ERROR_COOLDOWN)) {
    inErrorCooldown = false;
    errorShown = false;
    errorStatePersisted = false;
    Serial.println("Error cooldown period ended");
  }

  // Check if error state persistence has expired
  if (errorStatePersisted && (currentTime - errorPersistStartTime >= ERROR_PERSIST_TIME)) {
    errorStatePersisted = false;
    if (errorOccurred) {
      bpValue = "N/A"; // Clear error state after persistence period
      sendBPData(); // Send the cleared state
    }
  }

  //--- Button Press Handling ---//
  if (!processStarted && !inCooldown && buttonPressed) {
    delay(50); // Debounce
    if (digitalRead(BUTTON_PIN) == LOW) {
      sendBuzzerCommand(1, 200);  // Single short beep for button press
      startProcess(currentTime);
    }
  }

  //--- Motor Control Logic ---//
  if (processStarted && !motorRunning && (currentTime - processStartTime >= START_DELAY)) {
    startMotor(currentTime);
  }

  //--- Interrupt Handling ---//
  if (motorRunning && buttonPressed) {
    delay(50); // Debounce
    if (digitalRead(BUTTON_PIN) == LOW) {
      sendBuzzerCommand(2, 200);  // Double beep for interruption
      interruptProcess(currentTime);
    }
  }

  //--- Measurement Completion ---//
  if (motorRunning && (currentTime - motorStartTime >= currentMotorRunTime)) {
    if (errorOccurred && !motorErrorComplete) {
      motorErrorComplete = true;
      completeMeasurement(currentTime);
    } else if (!errorOccurred) {
      completeMeasurement(currentTime);
    }
  }

  //--- Cooldown Reset ---//
  if (inCooldown && (currentTime - cooldownStartTime >= COOLDOWN_TIME)) {
    resetSystem();
  }
}

void startProcess(unsigned long currentTime) {
  processStarted = true;
  processStartTime = currentTime;
  motorErrorComplete = false;
  
  // Only generate error if we're not in error cooldown
  if (!inErrorCooldown) {
    // Generate a random number between 0 and 99
    int randomValue = analogRead(A0) % 100;
    // Set error if random value is less than 30 (30% chance)
    errorOccurred = (randomValue < 30);
  } else {
    errorOccurred = false; // Force no error during cooldown
  }
  
  currentMotorRunTime = errorOccurred ? MOTOR_ERROR_TIME : MOTOR_RUN_TIME;
  
  Serial.println(errorOccurred ? 
    "⚠️ Starting BP measurement (Error Simulated - 30% chance)" : 
    "✅ Starting BP measurement...");
}

void startMotor(unsigned long currentTime) {
  motorRunning = true;
  motorStartTime = currentTime;
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, HIGH); // Activate pump
  sendBuzzerCommand(1, 100);  // Short beep when motor starts
  Serial.println("⚙️ Motor running...");
}

void interruptProcess(unsigned long currentTime) {
  motorRunning = false;
  processStarted = false;
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  bpValue = "Interrupted";
  sendBPData();
  Serial.println("❌ Measurement interrupted!");
  inCooldown = true;
  cooldownStartTime = currentTime;
}

void completeMeasurement(unsigned long currentTime) {
  motorRunning = false;
  digitalWrite(IN1, LOW);
  digitalWrite(IN2, LOW);
  
  if (errorOccurred) {
    if (!errorShown) {
      bpValue = "ERROR_STATE:Please Stay Still When Digital Blood Pressure is working";
      errorShown = true;
      inErrorCooldown = true;
      errorCooldownStartTime = currentTime;
      errorStatePersisted = true;
      errorPersistStartTime = currentTime;
      
      // Send error state multiple times to ensure reception
      for (int i = 0; i < 3; i++) {
        sendBPData();
        delay(100);
      }
      sendBuzzerCommand(3, 200);  // Triple beep for error state
    } else {
      bpValue = "ERROR_STATE:Please wait for the next measurement";
      sendBPData();
    }
  } else {
    generateBloodPressure();
    sendBPData();
    sendBuzzerCommand(1, 500);  // Long beep for successful measurement
  }
  
  Serial.print("🩺 BP Result: ");
  Serial.println(bpValue);
  
  processStarted = false;
  inCooldown = true;
  cooldownStartTime = currentTime;
}

void generateBloodPressure() {
  int selector = analogRead(A1) % 100;
  int systolic, diastolic;

  if (selector < 65) { // Normal (65%)
    systolic = 110 + (analogRead(A2) % 11);  // 110–120
    diastolic = 70 + (analogRead(A3) % 11);  // 70–80
    bpValue = String(systolic) + "/" + String(diastolic);
  } 
  else if (selector < 85) { // Elevated (20%)
    systolic = 121 + (analogRead(A2) % 10);  // 121–130
    diastolic = 81 + (analogRead(A3) % 10);  // 81–90
    bpValue = String(systolic) + "/" + String(diastolic) + " (Elevated)";
  } 
  else { // High (15%)
    systolic = 140 + (analogRead(A2) % 21);  // 140–160
    diastolic = 91 + (analogRead(A3) % 10);  // 91–100
    bpValue = String(systolic) + "/" + String(diastolic) + " (High)";
  }
}

void sendBPData() {
  if (bpValue.length() > 0) {
    String dataToSend = "BP:" + bpValue;
    espSerial.println(dataToSend); // Send to ESP8266
    Serial.println("Sending to ESP8266: " + dataToSend); // Debug print
    
    // Wait a bit to ensure transmission
    delay(100);
    
    // Verify if data was sent
    if (espSerial.available()) {
      String response = espSerial.readStringUntil('\n');
      Serial.println("Response from ESP8266: " + response);
      
      // If it's an error message, send it again to ensure reception
      if (bpValue.startsWith("ERROR_STATE:")) {
        delay(100);
        espSerial.println(dataToSend);
        Serial.println("Resending error to ESP8266: " + dataToSend);
      }
    }
  }
}

void resetSystem() {
  inCooldown = false;
  bpValue = "ERROR_STATE:Please wait for the next measurement"; // Changed from N/A to proper error message
  motorErrorComplete = false;
  Serial.println("System ready for next reading.");
}