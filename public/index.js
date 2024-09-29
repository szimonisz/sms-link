document.addEventListener('DOMContentLoaded', () => {
    const radios = document.getElementsByClassName('type-radio');
    const urlContainer = document.getElementById('url-container');
    const fileContainer = document.getElementById('file-container');
    const fileInput = document.getElementById('file');
    const messageElement = document.getElementById('message');
    for (const radio of radios) {
        radio.addEventListener('input', (e) => {
            const { value } = e.target;
            if (value === 'url') {
                fileContainer.style.display = 'none';
                urlContainer.style.display = 'block';
            } else {
                urlContainer.style.display = 'none';
                fileContainer.style.display = 'block';
            }
        });
    }
    const form = document.getElementById('sms-form');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sendSMS();
    });

    function flashMessage(message) {
        messageElement.innerText = message;
        messageElement.className = 'fade';
        messageElement.className = '';
        setTimeout(() => {
            messageElement.className = 'fade';
        }, 3000);
    }

    async function sendSMS() {
        const formData = new FormData(form);
        if (formData.get('type') === 'url') {
            formData.delete('file');
        } else {
            formData.delete('url');
        }

        try {
            const response = await fetch("/sms.php", {
                method: "POST",
                body: formData,
            });
            if (!response.ok) {
                 throw new Error(`Response status ${response.status}`);
            }

            //const responseJSON = await response.json();
            //console.log(responseJSON);
            fileInput.value = [];
            flashMessage('Message sent');
        } catch (e) {
            console.error(e);
            flashMessage(`ERROR: ${e.message}`);
        }

    }

});
