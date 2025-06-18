//Library Assignment
#include <Wire.h> 
#include <Adafruit_MLX90614.h> 
#include <SoftwareSerial.h>

//Pin Assignment
#define ECG_PIN A0
#define LO_PLUS 10
#define LO_MINUS 11
#define RX_PIN 2  // Connected to ESP32's TX (GPIO17)
#define TX_PIN 3  // Connected to ESP32's RX (GPIO16)

SoftwareSerial mySerial(RX_PIN, TX_PIN);
Adafruit_MLX90614 mlx = Adafruit_MLX90614();

bool leadsOff = false;
int leadOffCounter = 0;

void setup() {
    Serial.begin(115200);
    mySerial.begin(115200);

    pinMode(ECG_PIN, INPUT);
    pinMode(LO_PLUS, INPUT);
    pinMode(LO_MINUS, INPUT);

    if (!mlx.begin()) {
        Serial.println("ERROR: Body Temperature Sensor Not Working!");
        while (1);
    }

    Serial.println("System Ready. for Getting ECG & Temperature...");
}

void loop() {
    int ecgValue = analogRead(ECG_PIN);
    float ecgHeartRate = random(60, 100);  // Simulated

    // Debounced Lead-Off Detection
    if (digitalRead(LO_PLUS) == HIGH || digitalRead(LO_MINUS) == HIGH) {
        leadOffCounter++;
        if (leadOffCounter > 3) leadsOff = true;
    } else {
        leadOffCounter = 0;
        leadsOff = false;
    }

    if (leadsOff) {
        Serial.println("Leads Off! Check Electrodes.");
        mySerial.println("0,0");
        delay(1000);
        return;
    }

    float bodyTemp = mlx.readObjectTempC();
    if (isnan(bodyTemp) || bodyTemp < 27 || bodyTemp > 45) {
        Serial.println("WARNING: Invalid temperature!");
        bodyTemp = 0;
    }

    Serial.print("ECG: "); Serial.print(ecgHeartRate);
    Serial.print(" BPM, Temp: "); Serial.print(bodyTemp);
    Serial.println(" °C");

    // Print data being sent to ESP32
    Serial.print("Sending to ESP32: ");
    Serial.print(ecgHeartRate);
    Serial.print(",");
    Serial.println(bodyTemp);

    mySerial.print(ecgHeartRate);
    mySerial.print(",");
    mySerial.println(bodyTemp);

    delay(1000);
}