<script>
    // Check if the browser supports the SpeechRecognition API
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();

    // DOM Elements
    const startBtn = document.getElementById('start-btn');
    const stopBtn = document.getElementById('stop-btn');
    const resultText = document.getElementById('result-text');
    const languageSelect = document.getElementById('language');
    const downloadBtn = document.getElementById('download-btn');
    const clearBtn = document.getElementById('clear-btn');

    // Set initial recognition settings
    recognition.lang = 'en-US'; // Default language
    recognition.interimResults = false;
    recognition.continuous = false; // Stop automatically after a pause

    // Start recording when the user clicks the button
    startBtn.addEventListener('click', () => {
        recognition.lang = languageSelect.value; // Set the selected language
        recognition.start();
        startBtn.disabled = true;
        stopBtn.disabled = false;
    });

    // Stop recording when the user clicks the button
    stopBtn.addEventListener('click', () => {
        recognition.stop();
        startBtn.disabled = false;
        stopBtn.disabled = true;
    });

    // When speech recognition provides results
    recognition.addEventListener('result', (event) => {
        const transcript = event.results[0][0].transcript;
        resultText.value += transcript + '\n'; // Append the transcript to the textarea

        // Send the transcribed text to PHP for saving
        fetch('save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'transcribed_text': transcript
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log("Text saved successfully!");
            } else {
                console.error("Error saving text:", data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });

    // Handle recognition end event
    recognition.addEventListener('end', () => {
        startBtn.disabled = false;
        stopBtn.disabled = true;
    });

    // Handle errors
    recognition.addEventListener('error', (event) => {
        alert("Error occurred: " + event.error);
    });

    // Function to download the transcribed text as a PDF
    downloadBtn.addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Add Noto Sans Devanagari font if Hindi is selected
        const hindiFont = ''; // Replace with actual base64 string

        if (languageSelect.value === 'hi-IN') {
            doc.addFileToVFS('NotoSansDevanagari.ttf', hindiFont);
            doc.addFont('NotoSansDevanagari.ttf', 'NotoSansDevanagari', 'normal');
            doc.setFont('NotoSansDevanagari');
        } else {
            doc.setFont('Helvetica'); // Default font
        }

        doc.text(resultText.value, 10, 10);
        doc.save('transcription.pdf'); // Save the PDF with the name 'transcription.pdf'
    });

    // Function to clear the transcribed text from the textarea
    clearBtn.addEventListener('click', () => {
        resultText.value = ''; // Clear the textarea
    });
</script>
