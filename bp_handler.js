// BP Handler Functions
async function fetchBPData() {
    try {
        console.log("Fetching BP data..."); // Debug log
        const bpResponse = await fetch("fetch_bp.php", { 
            cache: 'no-store',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        });
        const bpData = await bpResponse.json();
        console.log("BP Update - Raw Response:", bpResponse); // Debug log
        console.log("BP Update - Parsed Data:", bpData); // Debug log

        const bpElement = document.getElementById("bp");
        console.log("BP Element found:", bpElement); // Debug log

        if (bpData && bpData.bp) {
            console.log("Updating BP display with value:", bpData.bp); // Debug log
            const bpValue = bpData.bp.toString().replace(" mmHg", "");
            bpElement.innerText = bpValue + " mmHg";
            
            const [systolic, diastolic] = bpValue.split('/').map(Number);
            console.log("Parsed BP values - Systolic:", systolic, "Diastolic:", diastolic); // Debug log
            
            bpElement.parentElement.classList.remove('red', 'yellow', 'green');
            
            if (systolic >= 140 || diastolic >= 90) {
                bpElement.parentElement.classList.add('red');
            } else if (systolic >= 120 || diastolic >= 80) {
                bpElement.parentElement.classList.add('yellow');
            } else {
                bpElement.parentElement.classList.add('green');
            }

            // Update hidden email form field for BP
            document.getElementById("emailBp").value = bpValue;
        } else {
            console.log("No BP data available in response"); // Debug log
            bpElement.innerText = "N/A mmHg";
            bpElement.parentElement.classList.remove('red', 'yellow', 'green');
            document.getElementById("emailBp").value = "N/A";
        }
    } catch (error) {
        console.error("Error fetching BP data:", error);
    }
}

// Initialize BP functionality
document.addEventListener('DOMContentLoaded', () => {
    console.log("Initializing BP handler..."); // Debug log
    fetchBPData(); // Initial fetch
    setInterval(fetchBPData, 1000); // Fetch BP data every second
}); 