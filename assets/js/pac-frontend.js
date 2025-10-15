document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('pac-form');
    const resultDiv = document.getElementById('pac-result');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const productInput = document.getElementById('pac-product-id');
        const productId = productInput.value.trim();

        if (!productId) {
            resultDiv.innerHTML = '<p style="color:red;">Please enter a product ID.</p>';
            return;
        }

        resultDiv.innerHTML = '<p>Checking availability...</p>';

        try {
            const response = await fetch(pac_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pac_check_availability',
                    nonce: pac_ajax.nonce,
                    product_id: productId,
                }),
            });

            const data = await response.json();

            if (data.success) {
                resultDiv.innerHTML = `<p style="color:green;">${data.data}</p>`;
            } else {
                resultDiv.innerHTML = `<p style="color:red;">${data.data}</p>`;
            }
        } catch (error) {
            resultDiv.innerHTML = '<p style="color:red;">An error occurred while checking availability.</p>';
        }
    });
});
