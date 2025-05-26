<?php
include 'header.php'; // Include your navigation/header file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Vital: About Us</title>

    <!-- Favicons -->
    <link href="img/logo.png" rel="icon">
    <link href="img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Marcellus:wght@400&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="vendor/aos/aos.css" rel="stylesheet">
    <link href="vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="css/main.css" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        body {
            background: url('microcity.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.85);
        }
        #navmenu {
            background: none;
            box-shadow: none;
        }
        #navmenu ul {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
            align-items: center;
        }
        #navmenu ul li {
            display: block;
        }
        #navmenu ul li a {
            display: block;
            padding: 8px 18px;
            color: #222;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            background: none;
            border: none;
            transition: background 0.2s, color 0.2s;
        }
        #navmenu ul li a:hover,
        #navmenu ul li a.active {
            background: #22c55e;
            color: #fff !important;
        }
        .mobile-nav-toggle {
            font-size: 2rem;
            color: #222;
            cursor: pointer;
            display: none;
            background: none;
            border: none;
        }
        @media (max-width: 600px) {
            #navmenu ul {
                gap: 0.5rem;
                font-size: 0.95rem;
            }
            #navmenu ul li a {
                padding: 6px 10px;
                font-size: 0.95rem;
            }
        }
        .about-section {
            max-width: 950px;
            margin: 0 auto;
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(34,197,94,0.13);
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .about-section h1, .about-section h2 {
            color: #15803d;
        }
        .about-section ul {
            margin-left: 1.5rem;
        }
        .about-section .feature-list li {
            margin-bottom: 0.5rem;
        }
        .about-section .dev-list li {
            display: inline-block;
            margin: 0.25rem 0.5rem;
            background: #bbf7d0;
            padding: 0.5rem 1.2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            color: #166534;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(34,197,94,0.08);
        }
        .about-section .dev-list li:hover {
            background: #22c55e;
            color: #fff;
            transform: scale(1.05);
        }
        .about-section .about-quote {
            font-style: italic;
            color: #065f46;
            margin: 1.5rem 0;
            border-left: 4px solid #22c55e;
            padding-left: 1rem;
            background: #f0fdf4;
            border-radius: 0.5rem;
        }
        .about-section .timeline {
            border-left: 4px solid #22c55e;
            margin: 2rem 0 2rem 1rem;
            padding-left: 2rem;
        }
        .about-section .timeline-event {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .about-section .timeline-event:before {
            content: '';
            position: absolute;
            left: -2.1rem;
            top: 0.2rem;
            width: 1.2rem;
            height: 1.2rem;
            background: #22c55e;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #22c55e;
        }
        .about-section .timeline-event h4 {
            margin-bottom: 0.2rem;
            color: #15803d;
        }
        .about-section .icon-row {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            margin: 2rem 0;
        }
        .about-section .icon-row .icon-box {
            background: #f0fdf4;
            border-radius: 1rem;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 2px 8px rgba(34,197,94,0.08);
            text-align: center;
            transition: transform 0.2s;
        }
        .about-section .icon-row .icon-box:hover {
            transform: translateY(-8px) scale(1.05);
            background: #bbf7d0;
        }
        .about-section .icon-row i {
            font-size: 2.5rem;
            color: #22c55e;
            margin-bottom: 0.5rem;
        }
        .about-section .icon-row span {
            display: block;
            font-weight: 600;
            color: #166534;
        }
        .about-section .animated-bg {
            position: absolute;
            top: -60px;
            right: -60px;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, #bbf7d0 60%, #22c55e 100%);
            opacity: 0.18;
            z-index: 0;
            border-radius: 50%;
            animation: float 6s ease-in-out infinite alternate;
        }
        @keyframes float {
            0% { transform: translateY(0) scale(1);}
            100% { transform: translateY(30px) scale(1.1);}
        }
        /* --- Chat Modal Styles (copied from index.php) --- */
        .floating-chat-btn {
          position: fixed;
          bottom: 32px;
          left: 32px;
          z-index: 9999;
          width: 60px;
          height: 60px;
          background: #22c55e;
          border-radius: 50%;
          box-shadow: 0 4px 16px rgba(0,0,0,0.15);
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          transition: box-shadow 0.2s, background 0.2s;
          border: none;
        }
        .floating-chat-btn:hover {
          box-shadow: 0 8px 24px rgba(0,0,0,0.25);
          background: #16a34a;
        }
        .floating-chat-btn i {
          font-size: 2rem;
          color: #fff;
        }
        .chat-modal-overlay {
          position: fixed;
          z-index: 10000;
          left: 0; top: 0; width: 100vw; height: 100vh;
          background: rgba(0,0,0,0.25);
          display: flex;
          align-items: flex-end;
          justify-content: flex-start;
          pointer-events: auto;
        }
        .chat-modal {
          background: #fff;
          border-radius: 16px 16px 0 0;
          box-shadow: 0 8px 32px rgba(0,0,0,0.18);
          width: 340px;
          max-width: 95vw;
          margin: 0 0 24px 24px;
          display: flex;
          flex-direction: column;
          max-height: 70vh;
          overflow: hidden;
          animation: chatModalIn 0.2s;
        }
        @keyframes chatModalIn {
          from { transform: translateY(100px); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
        }
        .chat-modal-header {
          background: #22c55e;
          color: #fff;
          padding: 12px 16px;
          font-weight: 600;
          display: flex;
          justify-content: space-between;
          align-items: center;
        }
        .chat-modal-close {
          background: none;
          border: none;
          color: #fff;
          font-size: 1.5rem;
          cursor: pointer;
          line-height: 1;
        }
        .chat-modal-body {
          flex: 1 1 auto;
          padding: 16px;
          overflow-y: auto;
          background: #f8f9fa;
          font-size: 0.97rem;
        }
        .chat-message {
          margin-bottom: 10px;
          padding: 8px 12px;
          border-radius: 12px;
          max-width: 80%;
          word-break: break-word;
          clear: both;
        }
        .chat-message.bot {
          background: #22c55e;
          color: #fff;
          align-self: flex-start;
        }
        .chat-message.user {
          background: #fff;
          color: #222;
          border: 1px solid #e0e0e0;
          align-self: flex-end;
          margin-left: auto;
        }
        .chat-modal-footer {
          display: flex;
          gap: 8px;
          padding: 12px 16px;
          background: #fff;
          border-top: 1px solid #eee;
        }
        .chat-modal-footer input[type="text"] {
          flex: 1 1 auto;
          border-radius: 8px;
        }
        .chat-modal-footer button {
          border-radius: 8px;
          background: #22c55e;
          border: none;
          color: #fff;
          transition: background 0.2s;
        }
        .chat-modal-footer button:hover {
          background: #16a34a;
        }
        @media (max-width: 500px) {
          .chat-modal { width: 98vw; margin-left: 1vw; }
        }
    </style>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <header id="header" class="header d-flex align-items-center position-relative">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.php" class="logo d-flex align-items-center">
                <img src="img/logo.png" alt="AI Vital">
            </a>

            <nav id="navmenu">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="registration2.php">Registration</a></li>
                    <li><a href="userdata2.php">User Data</a></li>
                    <li><a href="live reading.php">Live-Reading</a></li>
                    <li><a href="about.php" class="active">About Us</a></li>
                </ul>
            </nav>

        </div>
    </header>
    <div class="bg-overlay min-h-screen">
        <div class="container mx-auto px-4 py-8">
            <section class="about-section" data-aos="fade-up" data-aos-duration="1200">
                <div class="animated-bg"></div>
                <h1 class="text-4xl font-bold text-green-700 mb-4 text-center" data-aos="fade-down" data-aos-delay="100">About AI-Vital</h1>
                <p class="text-lg text-gray-700 leading-relaxed text-center mb-6" data-aos="fade-up" data-aos-delay="200">
                    AI-Vital is an innovative health monitoring and diagnostic platform that leverages the power of artificial intelligence, IoT sensors, and web technology to provide real-time, accessible, and secure healthcare solutions for schools, clinics, and communities. Designed to seamlessly integrate with existing health infrastructure, AI-Vital continuously monitors vital signs such as heart rate, body temperature, and ECG, providing instant insights into the user’s health status. The platform’s intelligent algorithms can detect early signs of medical issues, alerting users or healthcare providers in real-time, ensuring timely intervention. With cloud-based data storage, AI-Vital ensures that health records are securely stored and easily accessible for authorized personnel, maintaining privacy while improving the quality of care. Whether in a school setting, a local clinic, or within a community, AI-Vital is committed to making healthcare more proactive, efficient, and affordable for everyone.
                </p>
                <div class="icon-row" data-aos="zoom-in" data-aos-delay="300">
                    <div class="icon-box">
                        <i class="bi bi-cpu"></i>
                        <span>AI Diagnosis</span>
                    </div>
                    <div class="icon-box">
                        <i class="bi bi-heart-pulse"></i>
                        <span>Live Monitoring</span>
                    </div>
                    <div class="icon-box">
                        <i class="bi bi-shield-lock"></i>
                        <span>Secure Data</span>
                    </div>
                    <div class="icon-box">
                        <i class="bi bi-person-badge"></i>
                        <span>RFID Login</span>
                    </div>
                </div>
                <div class="about-quote text-center" data-aos="fade-right" data-aos-delay="400">
                    "Empowering health through technology, one vital sign at a time."
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4 text-center border-b-2 border-green-700 inline-block" data-aos="fade-up" data-aos-delay="500">Key Features</h2>
                <ul class="feature-list list-disc list-inside text-left max-w-2xl mx-auto mt-2 mb-6" data-aos="fade-up" data-aos-delay="600">
                    <li><strong>AI-Powered Diagnosis:</strong> Instantly analyzes vital signs and symptoms to provide health assessments and recommendations.</li>
                    <li><strong>Real-Time Monitoring:</strong> Live ECG, blood pressure, SpO₂, pulse, and temperature readings with easy-to-understand dashboards.</li>
                    <li><strong>RFID-Based Patient Identification:</strong> Secure, contactless login and data retrieval for every user.</li>
                    <li><strong>Personal Health Records:</strong> Automatic storage and retrieval of results, with options to print or email summaries.</li>
                    <li><strong>Consultation & Feedback:</strong> AI-driven consult feature for symptom checking and health advice.</li>
                    <li><strong>School & Clinic Integration:</strong> Designed for seamless deployment in educational and healthcare settings.</li>
                </ul>
                <div class="timeline" data-aos="fade-left" data-aos-delay="700">
                    <div class="timeline-event">
                        <h4>2022: Project Inception</h4>
                        <p>AI-Vital was conceptualized as a capstone project to address the need for smarter, accessible health monitoring in schools.</p>
                    </div>
                    <div class="timeline-event">
                        <h4>2023: Development & Prototyping</h4>
                        <p>Hardware integration, AI model training, and web platform development. Multiple iterations and user testing in real-world settings.</p>
                    </div>
                    <div class="timeline-event">
                        <h4>2024: Launch & Community Impact</h4>
                        <p>AI-Vital is deployed in partner schools and clinics, empowering users and healthcare professionals with real-time, AI-driven insights.</p>
                    </div>
                    <div class="timeline-event">
                        <h4>2025: Expansion & Innovation</h4>
                        <p>AI-Vital expands its reach to more communities and institutions, introduces new AI features, and enhances integration with modern health technologies for even greater impact.</p>
                    </div>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4 text-center border-b-2 border-green-700 inline-block" data-aos="fade-up" data-aos-delay="800">Our Mission</h2>
                <p class="text-lg text-gray-700 leading-relaxed text-center mb-6" data-aos="fade-up" data-aos-delay="900">
                    To make advanced health monitoring and AI-driven diagnostics accessible to everyone, ensuring early detection, prevention, and better health outcomes for all, AI-Vital bridges the gap between technology and healthcare. By democratizing access to state-of-the-art health tools, it empowers individuals and communities to take proactive steps towards managing their health. The platform’s user-friendly interface allows anyone, regardless of technical expertise, to easily monitor their health metrics and receive actionable insights. AI-Vital’s focus on early detection ensures that potential health issues are identified before they become critical, leading to more effective preventive care. This approach not only enhances individual health but also contributes to reducing healthcare costs, improving quality of life, and fostering healthier communities across the globe.
                </p>
                <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4 text-center border-b-2 border-green-700 inline-block" data-aos="fade-up" data-aos-delay="1000">Meet the Developers</h2>
                <ul class="dev-list text-center mb-6" data-aos="zoom-in" data-aos-delay="1100">
                    <li>Enrico, Pamela R.</li>
                    <li>Fraginal, John Lester P.</li>
                    <li>Manuel, Jeralyn R.</li>
                    <li>Nuqui, Karylle S.</li>
                    <li>Sacdalan, Mariela C.</li>
                    <li>Serrano, Angela G.</li>
                </ul>
                <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4 text-center border-b-2 border-green-700 inline-block" data-aos="fade-up" data-aos-delay="1200">Technology Stack</h2>
                <div class="flex flex-wrap justify-center items-center gap-8 mb-6" data-aos="fade-up" data-aos-delay="1250">
                    <!-- AI/ML & Tools -->
                    <div class="flex flex-col items-center">
                        <img src="img/arduino-logo.svg" alt="Arduino IDE" class="w-12 h-12 mb-1" title="Arduino IDE">
                        <span class="text-xs font-semibold text-gray-700">Arduino IDE</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/bootstrap-logo.svg" alt="Bootstrap" class="w-12 h-12 mb-1" title="Bootstrap">
                        <span class="text-xs font-semibold text-gray-700">Bootstrap</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/css3-logo.svg" alt="CSS3" class="w-12 h-12 mb-1" title="CSS3">
                        <span class="text-xs font-semibold text-gray-700">CSS3</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/espressif-logo.svg" alt="ESPRESSIF SYSTEM" class="w-12 h-12 mb-1" title="ESPRESSIF SYSTEM">
                        <span class="text-xs font-semibold text-gray-700">ESPRESSIF</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/git-logo.svg" alt="Git" class="w-12 h-12 mb-1" title="Git">
                        <span class="text-xs font-semibold text-gray-700">Git</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/github-logo.svg" alt="GitHub" class="w-12 h-12 mb-1" title="GitHub">
                        <span class="text-xs font-semibold text-gray-700">GitHub</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/html5-logo.svg" alt="HTML5" class="w-12 h-12 mb-1" title="HTML5">
                        <span class="text-xs font-semibold text-gray-700">HTML5</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/javascript-logo.svg" alt="JavaScript" class="w-12 h-12 mb-1" title="JavaScript">
                        <span class="text-xs font-semibold text-gray-700">JavaScript</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/jquery-logo.svg" alt="jQuery" class="w-12 h-12 mb-1" title="jQuery">
                        <span class="text-xs font-semibold text-gray-700">jQuery</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/mysql-logo.svg" alt="MySQL" class="w-12 h-12 mb-1" title="MySQL">
                        <span class="text-xs font-semibold text-gray-700">MySQL</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/openai-logo.svg" alt="OpenAI" class="w-12 h-12 mb-1" title="OpenAI">
                        <span class="text-xs font-semibold text-gray-700">OpenAI</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/php-logo.svg" alt="PHP" class="w-12 h-12 mb-1" title="PHP">
                        <span class="text-xs font-semibold text-gray-700">PHP</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/python-logo.svg" alt="Python" class="w-12 h-12 mb-1" title="Python">
                        <span class="text-xs font-semibold text-gray-700">Python</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/tailwind-logo.svg" alt="Tailwind CSS" class="w-12 h-12 mb-1" title="Tailwind CSS">
                        <span class="text-xs font-semibold text-gray-700">Tailwind CSS</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <img src="img/xampp-logo.svg" alt="XAMPP" class="w-12 h-12 mb-1" title="XAMPP">
                        <span class="text-xs font-semibold text-gray-700">XAMPP</span>
                    </div>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mt-8 mb-4 text-center border-b-2 border-green-700 inline-block" data-aos="fade-up" data-aos-delay="1400">Why Choose AI-Vital?</h2>
                <ul class="feature-list list-disc list-inside text-left max-w-2xl mx-auto mt-2 mb-6" data-aos="fade-up" data-aos-delay="1500">
                    <li>Fast, accurate, and user-friendly health monitoring</li>
                    <li>Secure and private data management</li>
                    <li>Designed for both medical professionals and everyday users</li>
                    <li>Continuous updates and improvements from a passionate team</li>
                </ul>
                <div class="about-quote text-center" data-aos="fade-left" data-aos-delay="1600">
                    "AI-Vital is more than a project — it’s our vision of a smarter, safer, and more connected future for healthcare."
                </div>
                <div class="text-center mt-8" data-aos="zoom-in" data-aos-delay="1700">
                    <a href="registration2.php" class="bg-green-500 text-white px-6 py-3 rounded-lg font-bold hover:bg-green-700 transition shadow-lg animate-bounce">Get Started</a>
                </div>
            </section>
        </div>
    </div>
    <footer id="footer" class="footer dark-background">
        <div class="footer-top">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6 footer-about">
                        <a href="index.php" class="logo d-flex align-items-center">
                            <span class="sitename">AI-VITAL</span>
                        </a>
                        <div class="footer-contact pt-3">
                            <p>MICROCITY OF BUSINESS AND TECHNOLOGY, INC.</p>
                            <p>Narra St., Capitol Drive, Tenejero, Balanga, Bataan </p>
                            <p class="mt-3"><strong>Phone:</strong> <span>(047-) 275-0786 / 09811865703</span></p>
                            <p><strong>Email:</strong> <span>info@microcitycomputercollege.com</span></p>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Start Using </h4>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="registration2.php">Registration</a></li>
                            <li><a href="userdata2.php">User Data</a></li>
                            <li><a href="live reading.php">Live-Reading</a></li>
                            <li><a href="about.php">About Us</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>What is?</h4>
                        <ul>
                            <li><a href="https://en.wikipedia.org/wiki/Blood_pressure" target="_blank">...Blood Pressure</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Human_body_temperature" target="_blank">...Body Temperature</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Electrocardiography" target="_blank">...Electrocardiogram</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Oxygen_saturation_(medicine)" target="_blank">...Oxygen Saturation (spO2)</a></li>
                            <li><a href="https://en.wikipedia.org/wiki/Pulse" target="_blank">...Pulse Rate</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Hardware Used</h4>
                        <ul>
                            <li><a href="#">MLX90614</a></li>
                            <li><a href="#">AD8232</a></li>
                            <li><a href="#">MAX30100</a></li>
                            <li><a href="#">MFRC522</a></li>
                            <li><a href="#">ESP-32 Wroom</a></li>
                            <li><a href="#">ESP-8266 </a></li>
                            <li><a href="#">Arduino Uno</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-2 col-md-3 footer-links">
                        <h4>Tech Stack Used</h4>
                        <ul>
                            <li><a href="#">Languages: C++, Php, Javascript</a></li>
                            <li><a href="#">Frameworks/Libraries: Bootstrap, Tailwind CSS</a></li>
                            <li><a href="#">Data Base: MySQL</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="copyright text-center">
            <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">
                <div class="d-flex flex-column align-items-center align-items-lg-start">
                    <div>
                        © Copyright <strong><span>AI-Vital</span></strong>. All Rights Reserved
                    </div>
                    <div class="credits">
                        Designed by Einsbern</a>
                    </div>
                </div>

                <div class="social-links order-first order-lg-last mb-3 mb-lg-0">
                    <a href="https://www.microcitycollege.com/" target="_blank"><i class="bi bi-browser-chrome"></i></a>
                    <a href="https://www.facebook.com/microcity.balanga" target="_blank"><i class="bi bi-facebook"></i></a>
                    <a href="mailto:einsbernsystem@gmail.com?subject=Send%20Feedback%20to%20Developer"><i class="bi bi-envelope"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        AOS.init();
    </script>
    <!-- Floating Chat Button -->
    <a href="#" class="floating-chat-btn" title="Chat" id="openChatBtn">
      <i class="bi bi-chat-left-heart"></i>
    </a>
    <!-- Chat Modal -->
    <div id="chatModal" class="chat-modal-overlay" style="display:none;">
      <div class="chat-modal">
        <div class="chat-modal-header">
          <span>Live Chat</span>
          <button type="button" class="chat-modal-close" id="closeChatBtn" aria-label="Close">&times;</button>
        </div>
        <div class="chat-modal-body" id="chatBody">
          <div class="chat-message bot">Hello! How can I help you today?</div>
        </div>
        <form class="chat-modal-footer" id="chatForm" autocomplete="off">
          <input type="text" id="chatInput" class="form-control" placeholder="Type your message..." required />
          <button type="submit" class="btn btn-secondary">Send</button>
        </form>
      </div>
    </div>
    <script>
      // Chat Modal JS (copied from index.php)
      document.addEventListener('DOMContentLoaded', function() {
        var openBtn = document.getElementById('openChatBtn');
        var closeBtn = document.getElementById('closeChatBtn');
        var chatModal = document.getElementById('chatModal');
        var chatForm = document.getElementById('chatForm');
        var chatInput = document.getElementById('chatInput');
        var chatBody = document.getElementById('chatBody');

        openBtn.addEventListener('click', function(e) {
          e.preventDefault();
          chatModal.style.display = 'flex';
          setTimeout(function() { chatInput.focus(); }, 200);
        });
        closeBtn.addEventListener('click', function() {
          chatModal.style.display = 'none';
        });
        chatForm.addEventListener('submit', function(e) {
          e.preventDefault();
          var msg = chatInput.value.trim();
          if (!msg) return;
          // Add user message
          var userMsg = document.createElement('div');
          userMsg.className = 'chat-message user';
          userMsg.textContent = msg;
          chatBody.appendChild(userMsg);
          chatBody.scrollTop = chatBody.scrollHeight;
          chatInput.value = '';
          // Show loading message
          var botMsg = document.createElement('div');
          botMsg.className = 'chat-message bot';
          botMsg.textContent = "Thinking...";
          chatBody.appendChild(botMsg);
          chatBody.scrollTop = chatBody.scrollHeight;
          // Fetch AI reply from chatbot.php
          fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg })
          })
          .then(res => res.json())
          .then(data => {
            botMsg.textContent = data.reply ? data.reply : (data.error ? "Error: " + data.error : "No reply.");
            chatBody.scrollTop = chatBody.scrollHeight;
          })
          .catch(err => {
            botMsg.textContent = "Error: " + err;
            chatBody.scrollTop = chatBody.scrollHeight;
          });
        });
        // Optional: close modal when clicking outside
        chatModal.addEventListener('click', function(e) {
          if (e.target === chatModal) chatModal.style.display = 'none';
        });
      });
    </script>
</body>
</html>

<?php
include 'footer.php'; // Include your footer file if available
?>
