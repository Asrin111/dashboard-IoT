document.addEventListener("DOMContentLoaded", function () {
    let button = document.getElementById("mqtt-button");
    if (button) {
        button.addEventListener("click", async function () {
            let message = document.getElementById("mqtt-message").value;

            if (!message.trim()) {
                alert("Pesan tidak boleh kosong!");
                return;
            }

            try {
                let response = await fetch("/mqtt/publish", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify({
                        topic: "iot/smartHome0oa8gdj/pintu",
                        message: message,
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }

                let data = await response.json();
                alert(data.message || "Pesan berhasil dikirim!");

                // Kosongkan input setelah berhasil
                document.getElementById("mqtt-message").value = "";
            } catch (error) {
                console.error("Error:", error);
                alert("Gagal mengirim pesan MQTT!");
            }
        });
    }
});
