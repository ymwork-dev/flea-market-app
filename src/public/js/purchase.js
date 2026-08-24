document.addEventListener('DOMContentLoaded', function() {
    const paymentSelect = document.getElementById('payment-select');
    const displayPayment = document.getElementById('display-payment');
    const hiddenPayment = document.getElementById('hidden-payment');
    const purchaseBtn = document.getElementById('purchase-btn');

    // 共通の更新処理を関数にまとめる
    function updateDisplay(value) {
        if (!value) return;
        displayPayment.textContent = value;
        hiddenPayment.value = value;
        purchaseBtn.disabled = false;
    }

    if (paymentSelect) {
        // 1. ページ読み込み時に値があれば反映（住所変更後の戻り対策）
        if (paymentSelect.value) {
            updateDisplay(paymentSelect.value);
        }

        // 2. プルダウンを変更した時の処理
        paymentSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            updateDisplay(selectedValue);

            fetch('/purchase/payment/store-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ payment_method: selectedValue })
            });
        });
    }
});
