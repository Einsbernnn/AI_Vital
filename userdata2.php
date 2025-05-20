<?php
$Write="<?php $" . "UIDresult=''; " . "echo $" . "UIDresult;" . " ?>";
file_put_contents('UIDContainer.php', $Write);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    require 'database.php';
    $id = $_POST['delete_id'];
    try {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM health_diagnostics WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute([$id]);
        Database::disconnect();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!-- Remove viewport meta tag if you want to further enforce desktop layout -->
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>AI Vital: User Data</title>

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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        fadeIn: 'fadeIn 1.5s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    },
                },
            },
        };
    </script>
    <style>
        body {
            background: url('microcity.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .bg-overlay {
            background-color: rgba(255, 255, 255, 0.8);
        }
        /* Nav menu as links only, no button style */
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
        /* Responsive table container */
        .responsive-table {
            width: 100%;
            max-height: 500px; /* Set max height for vertical scroll */
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            background: white;
        }
        /* Responsive adjustments for iPad Mini and similar */
        @media (max-width: 900px) {
            .responsive-table table {
                min-width: 800px;
                font-size: 0.95rem;
            }
            .responsive-table th,
            .responsive-table td {
                padding: 8px 6px;
            }
            #searchInput {
                font-size: 1rem;
            }
        }
        @media (max-width: 600px) {
            .responsive-table table {
                min-width: 700px;
                font-size: 0.9rem;
            }
            .responsive-table th,
            .responsive-table td {
                padding: 6px 4px;
            }
        }
        /* Sticky header for vertical scroll */
        .responsive-table thead th {
            position: sticky;
            top: 0;
            background: #15803d;
            color: #fff;
            z-index: 2;
        }
        /* Optional: visually indicate scrollable area */
        .responsive-table {
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("searchInput");
            const tableRows = document.querySelectorAll("tbody tr");
            const noMatchRow = document.createElement("tr");
            noMatchRow.id = "noMatchRow";
            noMatchRow.innerHTML = `<td colspan="9" class="text-center text-red-500 font-bold">No Match</td>`;
            noMatchRow.style.display = "none";
            document.querySelector("tbody").appendChild(noMatchRow);

            searchInput.addEventListener("input", function () {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let matchFound = false;

                tableRows.forEach(row => {
                    const cells = Array.from(row.children);
                    const rowText = cells.map(cell => cell.textContent.toLowerCase()).join(" ");
                    if (searchTerm && rowText.includes(searchTerm)) {
                        row.style.display = "";
                        row.classList.add("bg-yellow-200");
                        matchFound = true;
                    } else if (!searchTerm) {
                        row.style.display = ""; // Reset to show all rows
                        row.classList.remove("bg-yellow-200");
                    } else {
                        row.style.display = "none";
                        row.classList.remove("bg-yellow-200");
                    }
                });

                noMatchRow.style.display = matchFound || !searchTerm ? "none" : "";
            });

            // Add delete functionality with confirmation dialog
            document.querySelectorAll("button[onclick^='deleteUser']").forEach(button => {
                button.addEventListener("click", async function () {
                    const userId = this.getAttribute("onclick").match(/'([^']+)'/)[1]; // Extract the string ID
                    const userName = this.closest("tr").querySelector("td:first-child").textContent.trim(); // Get the user's name
                    const confirmation = await Swal.fire({
                        title: `Are you sure?`,
                        text: `You want to delete ${userName}, ${userId}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33', // Red for "Yes, delete it!"
                        cancelButtonColor: '#28a745', // Green for "Cancel"
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    });

                    if (confirmation.isConfirmed) {
                        fetch("", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `delete_id=${encodeURIComponent(userId)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById(`row-${userId}`).remove();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: `${userName}, ${userId} has been deleted.`,
                                });
                            } else {
                                console.error("Error:", data.error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to delete the user. Please try again.',
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Error deleting user:", error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                            });
                        });
                    }
                });
            });
        });
    </script>
</head>
<body class="bg-gradient-to-r from-green-200 to-green-400 min-h-screen flex flex-col">
    <div class="bg-overlay min-h-screen">
        
    <!-- Header Section -->
    <header id="header" class="header d-flex align-items-center position-relative">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="index.php" class="logo d-flex align-items-center">
                <img src="img/logo.png" alt="AI Vital">
            </a>

            <nav id="navmenu">
                <ul>
                    <li><a href="index.php" class="">Home</a></li>
                    <li><a href="registration2.php" class="">Registration</a></li>
                    <li><a href="userdata2.php" class="active">User Data</a></li>
                    <li><a href="live reading.php" class="">Live-Reading</a></li>
                    <li><a href="about.php" class="">About Us</a></li>
                </ul>
            </nav>

        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-4">Registered Users</h2>
        <div class="mb-4">
            <input type="text" id="searchInput" placeholder="Search..." class="w-full p-2 border rounded">
        </div>
        <div class="responsive-table">
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-green-700 text-white">
                        <th class="border border-gray-300 px-4 py-2">Name</th>
                        <th class="border border-gray-300 px-4 py-2">ID</th>
                        <th class="border border-gray-300 px-4 py-2">Gender</th>
                        <th class="border border-gray-300 px-4 py-2">Email</th>
                        <th class="border border-gray-300 px-4 py-2">Mobile Number</th>
                        <th class="border border-gray-300 px-4 py-2">Age</th>
                        <th class="border border-gray-300 px-4 py-2">Height</th>
                        <th class="border border-gray-300 px-4 py-2">Weight</th>
                        <th class="border border-gray-300 px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include 'database.php';
                    $pdo = Database::connect();
                    $sql = 'SELECT * FROM health_diagnostics ORDER BY id DESC';
                    foreach ($pdo->query($sql) as $row) {
                        echo '<tr id="row-' . htmlspecialchars($row['id']) . '">';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['name']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['id']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['gender']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['email']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['mobile']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['age']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['height']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">'. htmlspecialchars($row['weight']) . '</td>';
                        echo '<td class="border border-gray-300 px-4 py-2">
                                <div class="flex space-x-2">
                                    <a class="bg-green-500 text-white px-3 py-1 rounded" href="user data edit page.php?id='.htmlspecialchars($row['id']).'">Edit</a>
                                    <button class="bg-red-500 text-white px-3 py-1 rounded" onclick="deleteUser(\''.htmlspecialchars($row['id']).'\')">Delete</button>
                                </div>
                              </td>';
                        echo '</tr>';
                    }
                    Database::disconnect();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer Section -->
    <footer id="footer" class="footer dark-background" style="margin-bottom: 60px;">
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
                            <li><a href="#">AD8323</a></li>
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
</body>
</html>