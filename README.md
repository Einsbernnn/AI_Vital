💡 AI-Vital
---

AI-Vital is an intelligent health monitoring system that leverages OpenAI’s API to provide real-time AI-assisted diagnoses based on biometric sensor data. It integrates sensors for ECG, blood pressure, body temperature, pulse rate, and SpO₂, and is powered by ESP32, ESP8266, and Arduino Uno communicating via UART. The system also features RFID-based user identification and a MySQL database to store diagnostic results. A web dashboard allows for centralized access, while automated email delivery ensures users receive their health insights instantly.

⸻

✨ Features
---

🤖 AI-Powered Diagnosis
---

Utilizes OpenAI’s API to analyze health metrics and deliver intelligent, real-time medical insights.

🩺 Blood Pressure Monitoring

Incorporates a digital sphygmomanometer to record both systolic and diastolic blood pressure.

💓 ECG Monitoring
---

Captures real-time ECG signals to detect irregular heart activity and abnormalities.

☁️ Real-Time Data Storage

Securely stores all sensor readings in a web-connected MySQL database.

🆔 RFID-Based Identification
---

Links health data to individual users via RFID, enabling personalized tracking and record management.

🫁 SpO₂ & Pulse Monitoring
---

Measures oxygen saturation and pulse rate using an integrated pulse oximeter.

🌡️ Temperature Sensing
---

Monitors body temperature using infrared sensors for fast, contactless detection.

📧 Email Notifications
---

Automatically sends users a summary of their diagnosis and vital signs through email
## 📄 License
---
MIT License

Copyright (c) 2025  (Einsbernnn)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND...

## ⚙️ Website Installation (PHP + MySQL)


This guide covers the installation of the **web-based interface** of AI-Vital, including the PHP backend, MySQL database, and dashboard interface.

> 🧠 *The ESP32, ESP8266, and Arduino Uno setup has a separate installation guide. Please refer to the image below for the complete wiring diagram, including sensor connections, power supply layout, and UART communication.*

---

### 1. Install XAMPP  
Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/).  
Launch XAMPP and start both **Apache** and **MySQL** services from the control panel.

### 2. Clone or Download the Repository  
Open your terminal or command prompt and run:
```bash
git clone https://github.com/Einsbernnn/AI_Vital.git
```
Or download the ZIP from GitHub and extract it manually.

### 3. Move the Project to XAMPP Directory  
Copy the entire `AI_Vital` project folder into your XAMPP web root:

- On **Windows**: `C:\xampp\htdocs\`  
- On **macOS**: `/Applications/XAMPP/htdocs/`

---

### 4. Import the Database  
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)  
2. Create a new database (e.g., `ai_vital`)  
3. Click the **Import** tab  
4. Choose the SQL file from the project folder (`ai_vital.sql`)  
5. Click **Go** to import the data structure

---

### 5. Configure the Database Connection  
Open the file `config.php` (or your DB config file) and update the connection settings as needed:

```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ai_vital';
```

---

### 6. Run the Web Application  
In your browser, navigate to:

```
http://localhost/AI_Vital
```

You should now see the AI-Vital homepage or dashboard.

---

📌 **Please refer to the image below** for the full wiring diagram of sensors, UART communication between ESP boards and Arduino Uno, and power supply distribution. And upload the code for each microcontroller inside the MCU folder of the project foder.
## 🔌 API Reference (Free Usage Guide)

AI-Vital integrates with **OpenAI** and **Cohere** APIs to generate AI-powered medical insights and classifications. This guide shows how to use both APIs **within their free-tier limits**.

---

### 🧠 OpenAI API (Free Plan)


OpenAI offers limited free usage under their free-tier account, ideal for basic testing and development.


**How to Get Started:**

1. Create a free account at [https://platform.openai.com](https://platform.openai.com)
2. Go to your [API Keys](https://platform.openai.com/account/api-keys)
3. Generate a secret key and copy it.

**Sample API Request:**

```http
POST https://api.openai.com/v1/chat/completions
```

**Headers:**
```http
Authorization: Bearer YOUR_OPENAI_API_KEY
Content-Type: application/json
```

**Body Example:**
```json
{
  "model": "gpt-3.5-turbo",
  "messages": [
    { "role": "system", "content": "You are a medical assistant." },
    { "role": "user", "content": "Patient's SpO2 is 91% with chest tightness." }
  ]
}
```

---

### ✨ Cohere API (Free Plan)

Cohere offers free access for developers with generous limits on their text classification and embedding APIs.

**How to Get Started:**

1. Create a free account at [https://dashboard.cohere.com](https://dashboard.cohere.com)
2. Go to the **API Keys** tab
3. Copy your free API key.

**Sample Classification Request:**

```http
POST https://api.cohere.ai/classify
```

**Headers:**
```http
Authorization: Bearer YOUR_COHERE_API_KEY
Content-Type: application/json
```

**Body Example:**
```json
{
  "inputs": ["Shortness of breath and dizziness"],
  "examples": [
    { "text": "Shortness of breath", "label": "Respiratory" },
    { "text": "High blood pressure", "label": "Cardiac" }
  ]
}
```

---

### 🔐 Best Practice

- Store your API keys securely in a `.env` or `config.php` file
- Never expose your API key in client-side (browser) code


---

📌 Both OpenAI and Cohere's free plans are enough to run test demos and basic features of this project.
## 🙏  Acknowledgements

This project was made possible with the help of the following platforms, libraries, and tools:

- [OpenAI](https://openai.com/) — for providing the GPT API used in AI-powered medical diagnostics  
- [Cohere](https://cohere.com/) — for enabling natural language classification and health tagging  
- [PHPMailer](https://github.com/PHPMailer/PHPMailer) — for sending automated diagnostic reports via email  
- [Arduino](https://www.arduino.cc/) — for microcontroller development and embedded logic  
- [C++](https://cplusplus.com/) — for writing firmware for ESP32, ESP8266, and Arduino Uno  
- [ESP32 / ESP8266 by Espressif](https://www.espressif.com/) — for IoT capabilities and sensor interfacing  
- [PHP](https://www.php.net/) & [MySQL](https://www.mysql.com/) — for backend scripting and data storage  
- [XAMPP](https://www.apachefriends.org/) — for running a local development server  
- [GitHub](https://github.com/) — for version control and project hosting  

---

### 👨‍💻 Built By

**Engr. John Paul Legaspi**  
GitHub: [@Einsbernnn](https://github.com/Einsbernnn)  
---
**Engr. Danilo Y. Nuñez II**
GitHub: [@Lev-ux](https://github.com/Lev-ux)  
--- 

📧 Email: einsbernsystem@gmail.com — *Open for collaboration or work opportunities*
---
## 📎 Appendix

### 🔧 Project Structure

```
AI_Vital/
├── api/                # API logic (OpenAI, Cohere integration)
├── assets/             # CSS, JS, images
├── config.php          # Database and API configuration
├── database/           # SQL export file (e.g., ai_vital.sql)
├── email/              # PHPMailer setup for sending diagnostics
├── index.php           # Main entry point
└── README.md
```

---

### 📡 Microcontroller Integration

- The system integrates ESP32, ESP8266, and Arduino Uno via **UART (Serial) communication**.
- Sensor data is collected and sent to the web interface in real time.
- Please refer to the **wiring diagram** below (or in `/docs/diagram.png`) for details on:
  - Sensor-to-board connections
  - Power supply distribution
  - UART TX/RX links between microcontrollers

---

### 📁 Important Files

- `ai_vital.sql` – SQL dump to initialize the MySQL database
- `config.php` – Edit this file to connect the site to your local database and enter your API keys
- `email/send.php` – Handles email sending via PHPMailer
- `arduino/` – (Optional) Contains C++ source code for ESP32, ESP8266, and Arduino Uno

---

### 🌐 Recommended Tools

- [Postman](https://www.postman.com/) – For testing API calls locally
- [Arduino IDE](https://www.arduino.cc/en/software) – For uploading firmware to ESP/Uno
- [VS Code](https://code.visualstudio.com/) – For editing PHP/C++ with useful extensions

---

### 📄 Notes

- Ensure your firewall or antivirus doesn't block local port 80 or 3306 (Apache/MySQL)
- PHP version 7.4+ is recommended for compatibility
- Always test the serial communication separately before full integration
## Badges

Add badges from somewhere like: [shields.io](https://shields.io/)

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![GPLv3 License](https://img.shields.io/badge/License-GPL%20v3-yellow.svg)](https://opensource.org/licenses/)
[![AGPL License](https://img.shields.io/badge/license-AGPL-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)

# 💡 AI-Vital

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Made with PHP](https://img.shields.io/badge/Made%20with-PHP-777bb4?logo=php&logoColor=white)](https://www.php.net/)
[![Arduino IDE](https://img.shields.io/badge/Microcontrollers-C++%20%2F%20Arduino-green?logo=arduino)](https://www.arduino.cc/)
[![OpenAI API](https://img.shields.io/badge/OpenAI-API-orange?logo=openai)](https://platform.openai.com/)
[![Built by Einsbernnn](https://img.shields.io/badge/Built%20by-John%20Paul%20Legaspi-black?style=flat&logo=github)](https://github.com/Einsbernnn)

> Smart Health Diagnostics with AI-Powered Insights — PHP, MySQL, ESP32/ESP8266, AI integration.## 🤝 Contributing

Contributions are welcome and appreciated! Whether it's a bug fix, new feature, or documentation improvement — feel free to get involved.

### 🛠 How to Contribute

1. **Fork the repository**
   - Click the “Fork” button on the top right of this repo.

2. **Clone your forked repo locally**
   ```bash
   git clone https://github.com/Einsbernnn/AI_Vital.git
   ```

3. **Create a new branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

4. **Make your changes**

5. **Commit your changes**
   ```bash
   git commit -m "Add: your message here"
   ```

6. **Push to your branch**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Open a Pull Request**
   - Go to your forked repository on GitHub
   - Click "Compare & pull request"
   - Describe your changes and submit

---

### 🧾 Contribution Guidelines

- Keep your code clean and commented.
- Follow the existing code structure and naming conventions.
- Clearly describe what your pull request does.
- For major changes, please open an issue first to discuss.

---

### 🙋 Need Help?

If you have any questions or suggestions, feel free to [open an issue](https://github.com/Einsbernnn/AI_Vital/issues) or reach out to me at:  
📧 **johnpaullgsp@gmail.com**## 📸 Screenshots

Below are sample screenshots of the AI-Vital system, showing various components of the interface and data output:


![AI33](WebsitePreview/AI33.PNG)
![AI32](WebsitePreview/AI32.PNG)
![AI31](WebsitePreview/AI31.PNG)
![AI30](WebsitePreview/AI30.PNG)
![AI29](WebsitePreview/AI29.PNG)
![AI28](WebsitePreview/AI28.PNG)
![AI27](WebsitePreview/AI27.PNG)
![AI26](WebsitePreview/AI26.PNG)
![AI25](WebsitePreview/AI25.PNG)
![AI24](WebsitePreview/AI24.PNG)
![AI23](WebsitePreview/AI23.PNG)
![AI22](WebsitePreview/AI22.PNG)
![AI21](WebsitePreview/AI21.PNG)
![AI20](WebsitePreview/AI20.PNG)
![AI19](WebsitePreview/AI19.PNG)
![AI18](WebsitePreview/AI18.PNG)
![AI17](WebsitePreview/AI17.PNG)
![AI16](WebsitePreview/AI16.PNG)
![AI15](WebsitePreview/AI15.PNG)
![AI14](WebsitePreview/AI14.PNG)
![AI13](WebsitePreview/AI13.PNG)
![AI12](WebsitePreview/AI12.PNG)
![AI11](WebsitePreview/AI11.PNG)
![AI10](WebsitePreview/AI10.PNG)
![AI9](WebsitePreview/AI9.PNG)
![AI8](WebsitePreview/AI8.PNG)
![AI7](WebsitePreview/AI7.PNG)
![AI6](WebsitePreview/AI6.PNG)
![AI5](WebsitePreview/AI5.PNG)
![AI4](WebsitePreview/AI4.PNG)
![AI3](WebsitePreview/AI3.PNG)
![AI2](WebsitePreview/AI2.PNG)
![AI1](WebsitePreview/AI1.PNG)
## 🧰 Tech Stack



**Frontend / Interface:**  
- PHP (Web Logic & UI Rendering)  
- CSS and BootStrap (UI Styling & Layout)  

**Backend / Server:**  
- XAMPP (Apache + MySQL)  
- PHP (Server-side Logic)  
- PHPMailer (Email Handling)

**Embedded Systems:**  
- C++ (Arduino / ESP32 / ESP8266 Programming)  
- UART Communication Protocol

**AI / APIs:**  
- OpenAI GPT (AI-Powered Diagnosis)  
- Cohere (Text Classification & Tagging)

**Server:** XAMPP

