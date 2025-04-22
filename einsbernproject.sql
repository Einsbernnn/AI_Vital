-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 22, 2025 at 05:11 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `einsbernproject`
--

-- --------------------------------------------------------

--
-- Table structure for table `health_data`
--

CREATE TABLE `health_data` (
  `record_id` int(11) NOT NULL,
  `id` varchar(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `height` float DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `body_temperature` float DEFAULT NULL,
  `blood_pressure` varchar(10) DEFAULT NULL,
  `ecg` float DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `spo2` float DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_diagnostics`
--

CREATE TABLE `health_diagnostics` (
  `id` varchar(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `height` float DEFAULT NULL,
  `weight` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_diagnostics`
--

INSERT INTO `health_diagnostics` (`id`, `name`, `gender`, `email`, `mobile`, `age`, `height`, `weight`) VALUES
('657404', 'Elena Mendoza', 'Female', 'elena@email.com', '09691234562', 35, 165.5, 58.9),
('73b614', 'Maria Santos', 'Female', 'maria@email.com', '09281234568', 25, 160.2, 54.3),
('762104', 'Daniel Cruz', 'Male', 'daniel@email.com', '09591234561', 27, 180.3, 78.5),
('87b504', 'Joshua Fernandez', 'Male', 'joshua@email.com', '09201234567', 26, 174.6, 70.7),
('8c3814', 'Roberto Diaz', 'Male', 'roberto@email.com', '09791234563', 40, 172.4, 82.3),
('bb35fc3', 'Sophia Ramos', 'Female', 'sophia@email.com', '09891234564', 22, 162, 49.8),
('bd1824', 'John Paul Legaspi', 'Male', 'jampollegaspi18@gmail.com', '09463845548', 31, 167.8, 60.2),
('c36314', 'ANA LOPEZO', 'Female', 'ana@email.com', '09491234560', 30, 158.7, 50.4),
('d331e230', 'Carlos Reyes', 'Male', 'carlos@email.com', '09391234569', 32, 175, 72.1),
('danilo12', 'DANILO NUNEZ II', 'Male', 'nunezdanilo123@gmail.com', '09271601963', 23, 156, 60),
('dc8014', 'BERLIN JUANITSAS', 'Male', 'juan@email.com', '09171234562', 28, 170.5, 65.2);

-- --------------------------------------------------------

--
-- Table structure for table `health_readings`
--

CREATE TABLE `health_readings` (
  `count` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `temperature` decimal(5,2) NOT NULL,
  `ecg_rate` decimal(5,2) NOT NULL,
  `pulse_rate` decimal(5,2) NOT NULL,
  `spo2_level` decimal(5,2) NOT NULL,
  `blood_pressure` varchar(10) NOT NULL,
  `diagnosis` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_readings`
--

INSERT INTO `health_readings` (`count`, `id`, `patient_name`, `temperature`, `ecg_rate`, `pulse_rate`, `spo2_level`, `blood_pressure`, `diagnosis`, `created_at`) VALUES
(3, 'bd1824', 'John Paul Legaspi', 38.00, 90.00, 90.00, 100.00, '140/90', '**Vital Sign Analysis:**\n\nBased on the provided vital signs, I will determine whether they are normal, borderline, or critical.\n\n**Body Temperature:** 38 °C (Borderline)\n\nA body temperature of 38 °C is slightly elevated, indicating a minor infection or inflammation.\n\n**ECG Rate and Pulse Rate:** 90 BPM\n\nBoth ECG and pulse rates are within the normal range, which suggests that there is no evidence of tachycardia or bradycardia.\n\n**SpO2 Level:** 100 %\n\nThe SpO2 level indicates adequate oxygen saturation in the blood, ruling out hypoxia.\n\n**Blood Pressure:** 140/90 mmHg (Borderline)\n\nA blood pressure reading of 140/90 mmHg is slightly elevated, indicating a risk of hypertension.\n\n**Height and Weight:**\n\nBMI calculation:\nWeight (60.2 kg) / Height squared (167.8 cm)^2 = 23.3\n\nThe calculated BMI of 23.3 falls into the normal range, indicating a healthy weight for this individual.\n\n**Potential Medical Conditions:**\n\nBased on these vital signs, I would consider the following potential medical conditions:\n\n* Minor infection or inflammation\n* Hypertension (borderline)\n\n**Causes:**\n\nThe elevated body temperature and borderline high blood pressure may be indicative of an underlying infection or inflammation. The normal ECG and pulse rates suggest that there is no immediate cardiovascular concern.\n\n**Possible Sickness:**\n**SEPSIS IS A POSSIBLE CONCERN, ESPECIALLY WITH THE BORDERLINE HIGH BLOOD PRESSURE AND FEVER.**\n\n**Medical Actions:**\n\n* Drink plenty of fluids to help regulate body temperature\n* Rest and avoid strenuous activities to reduce the risk of further complications\n* Monitor vital signs closely for any changes or deterioration\n\n**Lifestyle Advice:**\n\n* Practice good hygiene to prevent the spread of infection\n* Maintain a healthy diet and regular exercise routine to promote overall well-being\n\n**Empathy:**\nI understand that you may be feeling unwell, and it\'s essential to prioritize your health. Please seek medical attention if you experience any worsening symptoms or concerns.\n\n**Urgent Medical Advice:**\n\nIf you experience severe chest pain, difficulty breathing, or severe headache, **SEEK EMERGENCY CARE IMMEDIATELY**.\n\n**School Nurse Availability:**\nRemember that the school nurse is available to assist you and provide guidance on how to manage your condition. Don\'t hesitate to reach out if you need support.\n\n**Important Note:**\n\nThe diagnosis provided is based solely on the data entered and is not 100% accurate. It\'s essential to seek medical attention from a qualified healthcare professional for an official diagnosis and treatment plan.', '2025-04-12 05:06:33'),
(4, 'bd1824', 'John Paul Legaspi', 38.00, 90.00, 90.00, 100.00, '140/90', '**ASSESSMENT AND ANALYSIS**\n\nBased on the provided vital signs, I will determine whether they are normal, borderline, or critical.\n\n**Body Temperature:** 38°C (Normal range: 36.5-37.5°C) - **CRITICAL**\n\nThe elevated body temperature indicates a possible infection or inflammation.\n\n**ECG Rate and Pulse Rate:** Both at 90 BPM (Normal range: 60-100 BPM) - **BORDERLINE**\n\nWhile the rates are within normal limits, they may still indicate mild stress or anxiety.\n\n**SpO2 Level:** 100% (Normal range: 95-100%) - **NORMAL**\n\nThe oxygen saturation level is within normal limits, indicating adequate oxygenation of the body.\n\n**Blood Pressure:** 140/90 mmHg (Normal range: 90-120/60-80 mmHg) - **CRITICAL**\n\nThe elevated blood pressure may indicate hypertension or a cardiovascular issue.\n\n**Height and Weight:** Calculated BMI is approximately 23.5 kg/m² (Normal range: 18.5-24.9 kg/m²) - **NORMAL**\n\n**CAUSES BEHIND ABNORMAL READINGS**\n\nBased on the provided vital signs, potential medical conditions that may be causing the abnormal readings are:\n\n* **Fever:** Elevated body temperature could indicate an underlying infection or inflammation.\n* **Potential Arrhythmia:** The borderline ECG rate and pulse rate may suggest mild stress or anxiety.\n\n**POSSIBLE SICKENESS BASED ON VITAL SIGNS RESULT**\n\n**YOU MAY BE AT RISK FOR HYPERTENSION, CARDIOVASCULAR ISSUES, OR INFECTIONS.**\n\n**MEDICAL ACTIONS**\n\nBased on the analysis, I recommend:\n\n* **Drink plenty of fluids** to help regulate body temperature and blood pressure.\n* **Rest and avoid strenuous activities** to reduce stress and anxiety.\n* **Monitor vital signs regularly** to track any changes or developments.\n\n**LIFESTYLE ADVICE**\n\nTo maintain good health, consider:\n\n* **Regular exercise** to improve cardiovascular function and overall well-being.\n* **Healthy eating habits** to manage blood pressure and weight.\n* **Stress management techniques**, such as meditation or deep breathing, to reduce anxiety.\n\n**EMPATHY**\n\nI understand that these abnormal readings may be concerning. Please remember that this analysis is based on the provided data and is not 100% accurate. If you are experiencing any symptoms or concerns, I strongly recommend seeking medical attention from a qualified healthcare professional.\n\n**URGENT MEDICAL ADVICE**\n\nIf your vital signs continue to deteriorate or you experience severe symptoms such as chest pain, shortness of breath, or severe headache, **SEEK EMERGENCY CARE IMMEDIATELY**.\n\n**BMI CALCULATION**\n\nYour calculated BMI is approximately 23.5 kg/m², indicating a normal weight range for your height.\n\n**REMEMBER**\n\nThe school nurse is available to assist you with any questions or concerns. This analysis is based on the provided data and is not 100% accurate. If you are experiencing any symptoms or concerns, I strongly recommend seeking medical attention from a qualified healthcare professional.', '2025-04-12 05:07:30'),
(5, 'bd1824', 'John Paul Legaspi', 38.00, 90.00, 90.00, 100.00, '140/90', '**Initial Assessment:**\n\nBased on the provided vital signs, I will assess their health status as follows:\n\n1. **Critical**: The patient\'s body temperature (38°C) and blood pressure (140/90 mmHg) indicate a critical situation.\n2. Potential medical conditions:\n	* Fever: **LIKELY**\n	* Hypertension: **LIKELY** (140/90 mmHg is above normal range)\n3. Causes behind abnormal readings:\n	* Fever may be caused by an underlying infection or inflammation.\n	* Hypertension may be caused by lifestyle factors, such as poor diet, lack of exercise, or excessive stress.\n\n**Possible Sickeness:**\n**SEPSIS** is a possibility based on the elevated body temperature and blood pressure. Sepsis is a life-threatening condition that occurs when an infection triggers a chain reaction throughout the body.\n\n**Medical Actions:**\n\n1. **Seek emergency care**: The patient\'s critical vital signs warrant immediate medical attention.\n2. Drink fluids to stay hydrated, as fever can lead to dehydration.\n3. Rest and avoid strenuous activities to help manage symptoms.\n\n**Disease Indication:**\nBased on the provided vitals, it is possible that the patient may be experiencing an infection or inflammation, which could be related to sepsis.\n\n**Urgent Medical Advice:**\nIf the patient experiences severe symptoms such as difficulty breathing, chest pain, or severe headache, they should seek immediate medical attention.\n\n**BMI Calculation:**\nBased on the provided height and weight, the patient\'s BMI is **23.4**, which falls within the normal range (18.5-24.9).\n\n**Important Notes:**\n\n* This diagnosis is based solely on the provided vital signs and is not 100% accurate.\n* It is recommended that the user seek medical attention if they are experiencing any concerning symptoms or if their condition worsens over time.\n* The school nurse is available to assist with further guidance and support.\n\nPlease note that this assessment is for general purposes only, and a qualified healthcare professional should be consulted for a proper diagnosis and treatment.', '2025-04-21 11:30:06'),
(6, 'bd1824', 'John Paul Legaspi', 38.00, 90.00, 90.00, 100.00, '140/90', '**Vital Sign Analysis**\n\nBased on the provided vital signs, I would categorize them as **borderline**.\n\n**Potential Medical Conditions:**\n\n* Fever (Body Temperature: 38 °C)\n* Elevated Blood Pressure (140/90 mmHg)\n\n**Causes:**\nThe elevated body temperature could be due to a viral or bacterial infection. The high blood pressure may be caused by factors such as stress, physical inactivity, or underlying medical conditions like hypertension.\n\n**Possible Sickness:**\n**PLEASE SEEK IMMEDIATE MEDICAL ATTENTION IF YOU EXPERIENCE SEVERE THROBBING HEADACHES, CHEST PAIN, SHORTNESS OF BREATH, OR DIZZINESS!**\n\nBased on the user\'s vital signs, I would suggest:\n\n* Drinking plenty of fluids to stay hydrated\n* Getting adequate rest to help manage stress and blood pressure\n* Consulting a doctor if symptoms persist or worsen\n\n**Possible Disease Indication:**\nThe combination of fever and elevated blood pressure may indicate an underlying infection or cardiovascular condition. However, this is not a definitive diagnosis and requires further evaluation by a medical professional.\n\n**Urgent Medical Advice:**\nIf you experience any severe symptoms such as chest pain, shortness of breath, or severe headache, **PLEASE SEEK EMERGENCY CARE IMMEDIATELY!**\n\n**BMI Calculation:**\nBased on the user\'s height (167.8 cm) and weight (60.2 kg), their BMI is approximately 22.3, which falls within the normal range.\n\n**Important Reminders:**\nThe diagnosis provided is based solely on the data provided and should not be considered a definitive medical diagnosis. It is essential to seek medical attention if you experience any concerning symptoms or have questions regarding your health. Additionally, please note that this AI nurse is available to assist you, but it is not a substitute for professional medical care. **SEEK MEDICAL ATTENTION IF NEEDED!**', '2025-04-21 11:34:50'),
(7, 'bd1824', 'John Paul Legaspi', 38.00, 90.00, 90.00, 100.00, '140/90', '**Vital Sign Analysis:**\n\nBased on the provided vital signs, I would categorize them as **borderline**.\n\n**Potential Medical Conditions:**\n\n1. Fever (Body Temperature: 38 °C): This suggests an underlying infection or inflammation.\n2. Hypertension (Blood Pressure: 140/90 mmHg): Elevated blood pressure can increase the risk of cardiovascular disease and stroke.\n\n**Causes behind abnormal readings:**\n\n* Fever: May be caused by a viral or bacterial infection, such as pneumonia, sinusitis, or tonsillitis.\n* Hypertension: Can be caused by a combination of genetic and lifestyle factors, including obesity, physical inactivity, and poor diet.\n\n**Possible Sickness based on user vital signs result:** **URGENT MEDICAL ATTENTION REQUIRED**\n\nThe abnormal readings suggest that the user may have an underlying infection or inflammation. It is crucial to seek medical attention as soon as possible to rule out any potential complications.\n\n**Suggested Medical Actions:**\n\n1. Seek immediate medical attention to determine the cause of the fever and hypertension.\n2. Rest and avoid strenuous activities until further evaluation and treatment are complete.\n3. Drink plenty of fluids to help manage symptoms and prevent dehydration.\n\n**Possible Indication on what are their disease:** Based on the provided vital signs, it is possible that the user may be experiencing a respiratory infection or an inflammatory condition, such as pneumonia or sinusitis.\n\n**Urgent Medical Advice:**\n\nIf you are experiencing severe symptoms such as difficulty breathing, chest pain, or severe headache, seek emergency medical attention immediately.\n\n**BMI Calculation:** Based on the provided height and weight, the user\'s BMI is approximately 22.3, which falls within the normal range.\n\n**Diagnosis:**\n\nBased on the provided vital signs, I would diagnose the user with a fever and hypertension of uncertain cause. However, this diagnosis is based solely on the data provided and should not be considered definitive. It is essential to seek medical attention for further evaluation and treatment.\n\n**Reminders:**\n\n* The school nurse is available to assist you.\n* If you are unsure about your condition or have concerns, please seek medical attention.\n\nPlease note that this analysis is based solely on the provided vital signs and should not be considered a definitive diagnosis. It is essential to consult with a medical professional for further evaluation and treatment.', '2025-04-21 17:46:21'),
(8, 'bd1824', 'John Paul Legaspi', 38.00, 90.00, 90.00, 100.00, '140/90', '**Vital Sign Analysis:**\n\nBased on the provided vital signs, I will determine the health status of the user.\n\n1. **Health Status:** Borderline\nThe user\'s temperature (38°C) is slightly elevated, indicating a potential infection or inflammation. The ECG and pulse rates are normal, but the blood pressure is mildly elevated (140/90 mmHg). The SpO2 level and height-weight ratio appear to be within normal limits.\n\n2. **Potential Medical Conditions:**\n	* Fever (38°C): May indicate an underlying infection, such as a respiratory or urinary tract infection.\n	* Elevated Blood Pressure (140/90 mmHg): May suggest hypertension or secondary hypertension due to another condition.\n\n3. **Causes behind Abnormal Readings:**\nThe elevated temperature and blood pressure may be related to an underlying infection, inflammation, or stress response.\n\n4. **Possible Sickness:** **HIGH BLOOD PRESSURE AND FEVER** - This combination could indicate a systemic issue that needs attention.\n5. **Medical Actions:**\n	* Drink plenty of fluids to help lower body temperature and blood pressure.\n	* Rest and avoid strenuous activities to reduce stress on the body.\n	* Monitor vital signs closely for any changes or deterioration.\n\n6. **Possible Disease Indication:** Hypertension, possibly with an underlying infection or inflammation.\n\n7. **Urgent Medical Advice:**\nIf symptoms worsen or persist, seek immediate medical attention.\n\n8. **BMI Calculation:**\nBased on the user\'s height (167.8 cm) and weight (60.2 kg), their BMI is approximately 22.5, which falls within the normal range (18.5-24.9).\n\n**Reminder:** This analysis is based on the provided data and should not be considered a definitive diagnosis. It is essential to consult with a healthcare professional for an accurate assessment.\n\nAs your virtual nurse, I am always available to assist you. If you have any questions or concerns, please don\'t hesitate to reach out. Remember, the school nurse is also available to provide support and guidance.', '2025-04-21 17:47:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `health_data`
--
ALTER TABLE `health_data`
  ADD PRIMARY KEY (`record_id`);

--
-- Indexes for table `health_diagnostics`
--
ALTER TABLE `health_diagnostics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `health_readings`
--
ALTER TABLE `health_readings`
  ADD PRIMARY KEY (`count`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `health_data`
--
ALTER TABLE `health_data`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `health_readings`
--
ALTER TABLE `health_readings`
  MODIFY `count` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
