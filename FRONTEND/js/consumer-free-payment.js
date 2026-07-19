/**
 * Consumer Free Payment UI
 *
 * Customer-facing payment methods (zero fee):
 * 1. Upload bukti transfer for bank transfer payments
 * 2. QRIS static QR code display for scanning
 * 3. Internal wallet - check balance, top-up, pay with wallet
 */

class ConsumerFreePayment {
    constructor() {
        this.wallet = null;
        this.qrisConfig = null;
    }

    // ===================================================================
    // WALLET
    // ===================================================================

    async loadWallet(customerId) {
        try {
            const result = await apiClient.getWallet(customerId);
            this.wallet = result.data;
            return this.wallet;
        } catch (err) {
            console.error('Wallet load error:', err);
            return null;
        }
    }

    renderWalletWidget() {
        if (!this.wallet) return '<div class="cfp-wallet-error">Unable to load wallet</div>';

        const balance = this.wallet.balance || 0;
        const available = this.wallet.available_balance || 0;
        const recent = this.wallet.recent_transactions || [];

        return `
            <div class="cfp-wallet-card">
                <div class="cfp-wallet-header">
                    <div class="cfp-wallet-icon">💳</div>
                    <div class="cfp-wallet-title">
                        <h3>My Wallet</h3>
                        <p class="cfp-wallet-number">${this.wallet.wallet_number}</p>
                    </div>
                </div>
                <div class="cfp-wallet-balance">
                    <div class="cfp-balance-row">
                        <span>Balance</span>
                        <strong>Rp ${this.formatRupiah(balance)}</strong>
                    </div>
                    <div class="cfp-balance-row">
                        <span>Available</span>
                        <strong class="cfp-available">Rp ${this.formatRupiah(available)}</strong>
                    </div>
                </div>
                <div class="cfp-wallet-actions">
                    <button class="cfp-btn cfp-btn-topup" onclick="consumerFreePayment.showTopupModal()">
                        <span>💰</span> Top Up
                    </button>
                    <button class="cfp-btn cfp-btn-history" onclick="consumerFreePayment.showTransactionHistory()">
                        <span>📋</span> History
                    </button>
                </div>
                ${recent.length > 0 ? `
                    <div class="cfp-wallet-recent">
                        <h4>Recent Transactions</h4>
                        ${recent.slice(0, 5).map(t => this.renderTransactionItem(t)).join('')}
                    </div>
                ` : ''}
            </div>
        `;
    }

    renderTransactionItem(t) {
        const isCredit = t.direction === 'credit';
        const sign = isCredit ? '+' : '-';
        const typeLabel = {
            topup: 'Top Up',
            payment: 'Payment',
            refund: 'Refund',
            transfer: 'Transfer'
        }[t.transaction_type] || t.transaction_type;

        return `
            <div class="cfp-txn-item ${t.direction}">
                <div class="cfp-txn-info">
                    <span class="cfp-txn-type">${typeLabel}</span>
                    <span class="cfp-txn-date">${new Date(t.created_at).toLocaleDateString('id-ID')}</span>
                </div>
                <div class="cfp-txn-amount ${t.direction}">
                    ${sign} Rp ${this.formatRupiah(t.amount)}
                </div>
            </div>
        `;
    }

    showTopupModal() {
        const modal = document.createElement('div');
        modal.className = 'cfp-modal-overlay';
        modal.id = 'cfp-topup-modal';
        modal.innerHTML = `
            <div class="cfp-modal">
                <div class="cfp-modal-header">
                    <h3>💰 Top Up Wallet</h3>
                    <button class="cfp-modal-close" onclick="consumerFreePayment.closeModal()">&times;</button>
                </div>
                <div class="cfp-modal-body">
                    <div class="cfp-topup-info">
                        <p class="cfp-help-text">Transfer ke rekening berikut, lalu upload bukti transfer:</p>
                        <div class="cfp-bank-info">
                            <div class="cfp-bank-row"><span>Bank:</span><strong>BCA</strong></div>
                            <div class="cfp-bank-row"><span>No. Rekening:</span><strong>1234567890</strong></div>
                            <div class="cfp-bank-row"><span>Atas Nama:</span><strong>Restoran ERP</strong></div>
                        </div>
                    </div>
                    <div class="cfp-form">
                        <div class="cfp-form-group">
                            <label>Amount (Rp) *</label>
                            <input type="number" id="cfp-topup-amount" class="cfp-input" placeholder="50000" min="10000">
                        </div>
                        <div class="cfp-form-group">
                            <label>Bank Asal</label>
                            <input type="text" id="cfp-topup-bank" class="cfp-input" placeholder="Mandiri / BRI / BNI">
                        </div>
                        <div class="cfp-form-group">
                            <label>Nama Pemilik Rekening</label>
                            <input type="text" id="cfp-topup-holder" class="cfp-input" placeholder="John Doe">
                        </div>
                        <div class="cfp-form-group">
                            <label>No. Referensi (opsional)</label>
                            <input type="text" id="cfp-topup-ref" class="cfp-input" placeholder="Transfer ref number">
                        </div>
                        <div class="cfp-form-group">
                            <label>Bukti Transfer *</label>
                            <input type="file" id="cfp-topup-file" class="cfp-file-input" accept="image/jpeg,image/png,image/webp,application/pdf">
                            <p class="cfp-help-text">JPG, PNG, WebP, atau PDF. Max 5MB.</p>
                        </div>
                        <button class="cfp-btn cfp-btn-submit" onclick="consumerFreePayment.submitTopup()">
                            Submit Top Up Request
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    async submitTopup() {
        const customerId = localStorage.getItem('customerId') || 1;
        const amount = parseFloat(document.getElementById('cfp-topup-amount').value);
        const fileInput = document.getElementById('cfp-topup-file');

        if (!amount || amount < 10000) {
            alert('Minimum top-up is Rp 10,000');
            return;
        }

        if (!fileInput.files[0]) {
            alert('Please upload proof of transfer');
            return;
        }

        const topupData = {
            customer_id: customerId,
            amount: amount,
            bank_from: document.getElementById('cfp-topup-bank').value,
            account_holder: document.getElementById('cfp-topup-holder').value,
            reference_number: document.getElementById('cfp-topup-ref').value
        };

        try {
            const result = await apiClient.requestWalletTopup(topupData, fileInput.files[0]);
            this.closeModal();
            if (result.success) {
                alert('Top-up request submitted! Your wallet will be credited after verification.');
                this.loadWallet(customerId).then(() => {
                    this.refreshWalletDisplay();
                });
            } else {
                alert('Failed: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Failed: ' + err.message);
        }
    }

    async showTransactionHistory() {
        const customerId = localStorage.getItem('customerId') || 1;

        try {
            const result = await apiClient.getWalletTransactions(customerId, { limit: 20 });
            const transactions = result.data?.data || [];

            const modal = document.createElement('div');
            modal.className = 'cfp-modal-overlay';
            modal.id = 'cfp-history-modal';
            modal.innerHTML = `
                <div class="cfp-modal">
                    <div class="cfp-modal-header">
                        <h3>📋 Transaction History</h3>
                        <button class="cfp-modal-close" onclick="consumerFreePayment.closeModal()">&times;</button>
                    </div>
                    <div class="cfp-modal-body">
                        ${transactions.length === 0 ? '<p class="cfp-empty">No transactions yet</p>' :
                            transactions.map(t => this.renderTransactionItem(t)).join('')}
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } catch (err) {
            alert('Failed to load history: ' + err.message);
        }
    }

    refreshWalletDisplay() {
        const el = document.getElementById('cfp-wallet-container');
        if (el && this.wallet) {
            el.innerHTML = this.renderWalletWidget();
        }
    }

    // ===================================================================
    // TRANSFER PROOF UPLOAD
    // ===================================================================

    showTransferProofModal(paymentId, orderId) {
        const modal = document.createElement('div');
        modal.className = 'cfp-modal-overlay';
        modal.id = 'cfp-transfer-modal';
        modal.innerHTML = `
            <div class="cfp-modal">
                <div class="cfp-modal-header">
                    <h3>🏦 Upload Bukti Transfer</h3>
                    <button class="cfp-modal-close" onclick="consumerFreePayment.closeModal()">&times;</button>
                </div>
                <div class="cfp-modal-body">
                    <div class="cfp-transfer-info">
                        <p class="cfp-help-text">Transfer ke rekening berikut lalu upload bukti:</p>
                        <div class="cfp-bank-info">
                            <div class="cfp-bank-row"><span>Bank:</span><strong>BCA</strong></div>
                            <div class="cfp-bank-row"><span>No. Rekening:</span><strong>1234567890</strong></div>
                            <div class="cfp-bank-row"><span>Atas Nama:</span><strong>Restoran ERP</strong></div>
                        </div>
                    </div>
                    <div class="cfp-form">
                        <div class="cfp-form-group">
                            <label>Bank Asal *</label>
                            <input type="text" id="cfp-transfer-bank" class="cfp-input" placeholder="Mandiri / BRI / BNI">
                        </div>
                        <div class="cfp-form-group">
                            <label>Nama Pemilik Rekening *</label>
                            <input type="text" id="cfp-transfer-holder" class="cfp-input" placeholder="John Doe">
                        </div>
                        <div class="cfp-form-group">
                            <label>No. Referensi (opsional)</label>
                            <input type="text" id="cfp-transfer-ref" class="cfp-input" placeholder="Transfer ref number">
                        </div>
                        <div class="cfp-form-group">
                            <label>Bukti Transfer *</label>
                            <input type="file" id="cfp-transfer-file" class="cfp-file-input" accept="image/jpeg,image/png,image/webp,application/pdf">
                            <p class="cfp-help-text">JPG, PNG, WebP, atau PDF. Max 5MB.</p>
                        </div>
                        <button class="cfp-btn cfp-btn-submit" onclick="consumerFreePayment.submitTransferProof(${paymentId}, ${orderId})">
                            Upload Bukti Transfer
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    async submitTransferProof(paymentId, orderId) {
        const fileInput = document.getElementById('cfp-transfer-file');
        const bankFrom = document.getElementById('cfp-transfer-bank').value;
        const holder = document.getElementById('cfp-transfer-holder').value;

        if (!bankFrom || !holder) {
            alert('Bank asal and account holder are required');
            return;
        }

        if (!fileInput.files[0]) {
            alert('Please upload proof of transfer');
            return;
        }

        const formData = new FormData();
        formData.append('payment_id', paymentId);
        formData.append('order_id', orderId);
        formData.append('bank_from', bankFrom);
        formData.append('account_holder', holder);
        formData.append('reference_number', document.getElementById('cfp-transfer-ref').value || '');
        formData.append('file', fileInput.files[0]);

        try {
            const result = await apiClient.uploadTransferProof(formData);
            this.closeModal();
            if (result.success) {
                alert('Bukti transfer uploaded! Waiting for verification by kasir.');
            } else {
                alert('Failed: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Failed: ' + err.message);
        }
    }

    // ===================================================================
    // QRIS PAYMENT
    // ===================================================================

    async showQrisPaymentModal(amount, orderId) {
        try {
            const result = await apiClient.generateQrisPayment(amount, orderId);
            const d = result.data;

            if (!d) {
                alert('QRIS not configured for this restaurant');
                return;
            }

            const modal = document.createElement('div');
            modal.className = 'cfp-modal-overlay';
            modal.id = 'cfp-qris-modal';
            modal.innerHTML = `
                <div class="cfp-modal cfp-qris-modal">
                    <div class="cfp-modal-header">
                        <h3>📱 QRIS Payment</h3>
                        <button class="cfp-modal-close" onclick="consumerFreePayment.closeModal()">&times;</button>
                    </div>
                    <div class="cfp-modal-body cfp-qris-body">
                        <div class="cfp-qris-merchant">
                            <p>${d.merchant_name}</p>
                        </div>
                        <div class="cfp-qris-amount-display">
                            <span>Amount to Pay</span>
                            <strong>Rp ${this.formatRupiah(d.amount)}</strong>
                        </div>
                        <div class="cfp-qris-code">
                            <div class="cfp-qr-placeholder">
                                <p>📷</p>
                                <p class="cfp-qr-help">Scan this QR code with any e-wallet or mobile banking app that supports QRIS</p>
                            </div>
                        </div>
                        <div class="cfp-qris-string">
                            <label>QRIS String (for manual input):</label>
                            <textarea readonly rows="3" onclick="this.select()">${d.qr_content}</textarea>
                        </div>
                        <div class="cfp-qris-info">
                            <div class="cfp-qris-info-row">
                                <span>Acquirer:</span><strong>${d.acquirer_bank || 'N/A'}</strong>
                            </div>
                            <div class="cfp-qris-info-row">
                                <span>MDR Fee:</span><strong>Rp ${this.formatRupiah(d.mdr_fee)}</strong>
                            </div>
                        </div>
                        <p class="cfp-qr-help">After payment, please inform the kasir to confirm your payment.</p>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        } catch (err) {
            alert('Failed to generate QRIS: ' + err.message);
        }
    }

    // ===================================================================
    // WALLET PAYMENT
    // ===================================================================

    async payWithWallet(customerId, orderId, amount) {
        if (!this.wallet) {
            await this.loadWallet(customerId);
        }

        const available = this.wallet?.available_balance || 0;
        if (available < amount) {
            alert(`Insufficient wallet balance. Available: Rp ${this.formatRupiah(available)}, Required: Rp ${this.formatRupiah(amount)}`);
            return false;
        }

        if (!confirm(`Pay Rp ${this.formatRupiah(amount)} from wallet balance?\nCurrent balance: Rp ${this.formatRupiah(this.wallet.balance)}`)) {
            return false;
        }

        try {
            const result = await apiClient.payWithWallet(customerId, orderId, amount);
            if (result.success) {
                alert(`Payment successful! New balance: Rp ${this.formatRupiah(result.data.wallet_balance_after)}`);
                await this.loadWallet(customerId);
                this.refreshWalletDisplay();
                return true;
            } else {
                alert('Payment failed: ' + (result.message || 'Unknown error'));
                return false;
            }
        } catch (err) {
            alert('Payment failed: ' + err.message);
            return false;
        }
    }

    // ===================================================================
    // PAYMENT METHOD SELECTOR
    // ===================================================================

    renderPaymentMethods(orderId, amount, customerId) {
        return `
            <div class="cfp-payment-methods">
                <h3>Pilih Metode Pembayaran</h3>
                <div class="cfp-method-list">
                    <div class="cfp-method-card" onclick="consumerFreePayment.payWithWallet(${customerId}, ${orderId}, ${amount})">
                        <div class="cfp-method-icon">💳</div>
                        <div class="cfp-method-info">
                            <strong>Wallet</strong>
                            <span>Saldo: Rp ${this.formatRupiah(this.wallet?.balance || 0)}</span>
                        </div>
                        <div class="cfp-method-badge">GRATIS</div>
                    </div>
                    <div class="cfp-method-card" onclick="consumerFreePayment.showQrisPaymentModal(${amount}, ${orderId})">
                        <div class="cfp-method-icon">📱</div>
                        <div class="cfp-method-info">
                            <strong>QRIS</strong>
                            <span>Scan QR code dengan e-wallet/banking</span>
                        </div>
                        <div class="cfp-method-badge">0.7% MDR</div>
                    </div>
                    <div class="cfp-method-card" onclick="consumerFreePayment.showTransferProofModal(0, ${orderId})">
                        <div class="cfp-method-icon">🏦</div>
                        <div class="cfp-method-info">
                            <strong>Bank Transfer</strong>
                            <span>Transfer + upload bukti</span>
                        </div>
                        <div class="cfp-method-badge">GRATIS</div>
                    </div>
                </div>
            </div>
        `;
    }

    // ===================================================================
    // HELPERS
    // ===================================================================

    closeModal() {
        const modal = document.querySelector('.cfp-modal-overlay');
        if (modal) modal.remove();
    }

    formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount || 0);
    }
}

window.consumerFreePayment = new ConsumerFreePayment();
