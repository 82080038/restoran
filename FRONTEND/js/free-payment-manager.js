/**
 * Free Payment Manager - Dashboard POS
 *
 * Manages zero-fee payment methods for kasir/admin:
 * 1. Transfer proof verification (approve/reject bukti transfer)
 * 2. QRIS static config & payment confirmation
 * 3. Wallet top-up verification
 */

class FreePaymentManager {
    constructor() {
        this.activeTab = 'transfer-proof';
        this.transferProofs = [];
        this.topupRequests = [];
        this.qrisConfig = null;
        this.walletInfo = null;
        this.isLoading = false;
    }

    /**
     * Render the main free payment dashboard panel
     */
    render() {
        return `
            <div class="free-payment-panel">
                <div class="fp-header">
                    <h2><i class="fas fa-wallet"></i> Free Payment Management</h2>
                    <p class="fp-subtitle">Zero-fee payment methods - no gateway charges</p>
                </div>

                <div class="fp-tabs">
                    <button class="fp-tab active" data-tab="transfer-proof" onclick="freePaymentManager.switchTab('transfer-proof')">
                        <i class="fas fa-file-invoice"></i> Bukti Transfer
                        <span class="fp-badge" id="fp-transfer-badge" style="display:none">0</span>
                    </button>
                    <button class="fp-tab" data-tab="qris" onclick="freePaymentManager.switchTab('qris')">
                        <i class="fas fa-qrcode"></i> QRIS Statis
                    </button>
                    <button class="fp-tab" data-tab="wallet" onclick="freePaymentManager.switchTab('wallet')">
                        <i class="fas fa-coins"></i> Wallet Top-up
                        <span class="fp-badge" id="fp-topup-badge" style="display:none">0</span>
                    </button>
                </div>

                <div class="fp-content" id="fp-content">
                    ${this.renderTransferProofTab()}
                </div>
            </div>
        `;
    }

    switchTab(tab) {
        this.activeTab = tab;
        document.querySelectorAll('.fp-tab').forEach(t => t.classList.remove('active'));
        document.querySelector(`.fp-tab[data-tab="${tab}"]`).classList.add('active');

        const content = document.getElementById('fp-content');
        switch(tab) {
            case 'transfer-proof':
                content.innerHTML = this.renderTransferProofTab();
                this.loadTransferProofs();
                break;
            case 'qris':
                content.innerHTML = this.renderQrisTab();
                this.loadQrisConfig();
                break;
            case 'wallet':
                content.innerHTML = this.renderWalletTab();
                this.loadTopupRequests();
                break;
        }
    }

    // ===================================================================
    // TAB 1: TRANSFER PROOF
    // ===================================================================

    renderTransferProofTab() {
        return `
            <div class="fp-tab-content">
                <div class="fp-toolbar">
                    <select id="fp-transfer-filter" onchange="freePaymentManager.loadTransferProofs()" class="fp-select">
                        <option value="">All Status</option>
                        <option value="pending" selected>Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button class="fp-btn fp-btn-refresh" onclick="freePaymentManager.loadTransferProofs()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
                <div id="fp-transfer-list" class="fp-list">
                    <div class="fp-loading">Loading...</div>
                </div>
            </div>
        `;
    }

    async loadTransferProofs() {
        const filter = document.getElementById('fp-transfer-filter')?.value || 'pending';
        const listEl = document.getElementById('fp-transfer-list');
        if (!listEl) return;

        listEl.innerHTML = '<div class="fp-loading">Loading...</div>';

        try {
            const result = await apiClient.getTransferProofs({ status: filter, limit: 50 });
            this.transferProofs = result.data?.data || [];
            this.updateBadges();

            if (this.transferProofs.length === 0) {
                listEl.innerHTML = '<div class="fp-empty">No transfer proofs found</div>';
                return;
            }

            listEl.innerHTML = this.transferProofs.map(p => this.renderTransferProofCard(p)).join('');
        } catch (err) {
            listEl.innerHTML = `<div class="fp-error">Failed to load: ${err.message}</div>`;
        }
    }

    renderTransferProofCard(proof) {
        const statusClass = `fp-status-${proof.verification_status}`;
        const statusLabel = proof.verification_status.charAt(0).toUpperCase() + proof.verification_status.slice(1);
        const date = new Date(proof.uploaded_at).toLocaleString('id-ID');

        let actions = '';
        if (proof.verification_status === 'pending') {
            actions = `
                <div class="fp-card-actions">
                    <button class="fp-btn fp-btn-approve" onclick="freePaymentManager.verifyProof(${proof.proof_id}, 'approve')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="fp-btn fp-btn-reject" onclick="freePaymentManager.verifyProof(${proof.proof_id}, 'reject')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            `;
        }

        return `
            <div class="fp-card ${statusClass}">
                <div class="fp-card-header">
                    <div class="fp-card-title">
                        <strong>Proof #${proof.proof_id}</strong>
                        <span class="fp-status-badge ${statusClass}">${statusLabel}</span>
                    </div>
                    <div class="fp-card-date">${date}</div>
                </div>
                <div class="fp-card-body">
                    <div class="fp-card-row">
                        <span>Payment:</span>
                        <strong>${proof.payment_number || '#' + proof.payment_id}</strong>
                    </div>
                    <div class="fp-card-row">
                        <span>Order:</span>
                        <strong>${proof.order_number || '#' + proof.order_id}</strong>
                    </div>
                    <div class="fp-card-row">
                        <span>Amount:</span>
                        <strong class="fp-amount">Rp ${this.formatRupiah(proof.transfer_amount)}</strong>
                    </div>
                    ${proof.bank_from ? `<div class="fp-card-row"><span>Bank:</span><strong>${proof.bank_from}</strong></div>` : ''}
                    ${proof.account_holder ? `<div class="fp-card-row"><span>Account:</span><strong>${proof.account_holder}</strong></div>` : ''}
                    ${proof.reference_number ? `<div class="fp-card-row"><span>Ref:</span><strong>${proof.reference_number}</strong></div>` : ''}
                </div>
                <div class="fp-card-footer">
                    <a href="/${proof.file_path}" target="_blank" class="fp-btn fp-btn-view">
                        <i class="fas fa-eye"></i> View Proof
                    </a>
                    ${actions}
                </div>
            </div>
        `;
    }

    async verifyProof(proofId, action) {
        const reason = action === 'reject'
            ? prompt('Reason for rejection:') || 'Transfer could not be verified'
            : null;

        if (action === 'approve' && !confirm('Approve this transfer proof? Payment will be marked as completed.')) return;

        try {
            await apiClient.verifyTransferProof(proofId, action, reason);
            this.loadTransferProofs();
            if (typeof showToast === 'function') {
                showToast(action === 'approve' ? 'Transfer proof approved' : 'Transfer proof rejected', 'success');
            }
        } catch (err) {
            if (typeof showToast === 'function') {
                showToast('Failed: ' + err.message, 'error');
            }
        }
    }

    // ===================================================================
    // TAB 2: QRIS STATIC
    // ===================================================================

    renderQrisTab() {
        return `
            <div class="fp-tab-content">
                <div id="fp-qris-config" class="fp-qris-section">
                    <div class="fp-loading">Loading QRIS config...</div>
                </div>
            </div>
        `;
    }

    async loadQrisConfig() {
        const el = document.getElementById('fp-qris-config');
        if (!el) return;

        try {
            const result = await apiClient.getQrisConfig();
            this.qrisConfig = result.data;

            if (!this.qrisConfig?.configured) {
                el.innerHTML = this.renderQrisSetupForm();
                return;
            }

            el.innerHTML = this.renderQrisConfigDisplay();
        } catch (err) {
            el.innerHTML = `<div class="fp-error">Failed to load QRIS config: ${err.message}</div>`;
        }
    }

    renderQrisSetupForm() {
        return `
            <div class="fp-qris-setup">
                <h3><i class="fas fa-qrcode"></i> Setup QRIS Statis</h3>
                <p class="fp-help">Scan QRIS statis dari bank acquirer Anda (BCA, Mandiri, BRI, BNI, dll) dan paste konten QR di sini.</p>
                <div class="fp-form">
                    <div class="fp-form-group">
                        <label>Merchant Name *</label>
                        <input type="text" id="fp-qris-merchant" class="fp-input" placeholder="Nama restoran/merchant">
                    </div>
                    <div class="fp-form-group">
                        <label>QR Content (MPAN string) *</label>
                        <textarea id="fp-qris-content" class="fp-textarea" rows="4" placeholder="00020101021226..."></textarea>
                    </div>
                    <div class="fp-form-row">
                        <div class="fp-form-group">
                            <label>Acquirer Bank</label>
                            <input type="text" id="fp-qris-bank" class="fp-input" placeholder="BCA / Mandiri / BRI / BNI">
                        </div>
                        <div class="fp-form-group">
                            <label>MDR Rate (%)</label>
                            <input type="number" id="fp-qris-mdr" class="fp-input" value="0.7" step="0.01" placeholder="0.7">
                        </div>
                    </div>
                    <div class="fp-form-row">
                        <div class="fp-form-group">
                            <label>Merchant ID</label>
                            <input type="text" id="fp-qris-mid" class="fp-input" placeholder="Optional">
                        </div>
                        <div class="fp-form-group">
                            <label>NMID</label>
                            <input type="text" id="fp-qris-nmid" class="fp-input" placeholder="Optional">
                        </div>
                    </div>
                    <button class="fp-btn fp-btn-primary" onclick="freePaymentManager.saveQrisConfig()">
                        <i class="fas fa-save"></i> Save QRIS Config
                    </button>
                </div>
            </div>
        `;
    }

    renderQrisConfigDisplay() {
        const c = this.qrisConfig;
        return `
            <div class="fp-qris-display">
                <div class="fp-qris-info">
                    <h3><i class="fas fa-qrcode"></i> QRIS Configuration</h3>
                    <div class="fp-card-row"><span>Merchant:</span><strong>${c.merchant_name}</strong></div>
                    <div class="fp-card-row"><span>Acquirer:</span><strong>${c.acquirer_bank || 'N/A'}</strong></div>
                    <div class="fp-card-row"><span>MDR Rate:</span><strong>${c.mdr_rate}%</strong></div>
                    <div class="fp-card-row"><span>Status:</span><strong class="fp-status-active">Active</strong></div>
                </div>
                <div class="fp-qris-actions">
                    <button class="fp-btn fp-btn-secondary" onclick="freePaymentManager.showQrisGenerate()">
                        <i class="fas fa-plus"></i> Generate QR for Payment
                    </button>
                    <button class="fp-btn fp-btn-edit" onclick="freePaymentManager.editQrisConfig()">
                        <i class="fas fa-edit"></i> Edit Config
                    </button>
                </div>
                <div id="fp-qris-generate" style="display:none" class="fp-qris-generate">
                    <h4>Generate QRIS Payment</h4>
                    <div class="fp-form-row">
                        <div class="fp-form-group">
                            <label>Amount (Rp)</label>
                            <input type="number" id="fp-qris-amount" class="fp-input" placeholder="50000">
                        </div>
                        <div class="fp-form-group">
                            <label>Order ID (optional)</label>
                            <input type="number" id="fp-qris-order" class="fp-input" placeholder="123">
                        </div>
                    </div>
                    <button class="fp-btn fp-btn-primary" onclick="freePaymentManager.generateQris()">Generate</button>
                    <div id="fp-qris-result"></div>
                </div>
                <div class="fp-qris-confirm-section">
                    <h4>Confirm QRIS Payment</h4>
                    <p class="fp-help">After customer pays via QRIS scan, confirm the payment manually after checking your bank statement.</p>
                    <div class="fp-form-row">
                        <div class="fp-form-group">
                            <label>Payment ID</label>
                            <input type="number" id="fp-qris-confirm-pid" class="fp-input" placeholder="Payment ID">
                        </div>
                        <div class="fp-form-group">
                            <label>Reference Number (optional)</label>
                            <input type="text" id="fp-qris-confirm-ref" class="fp-input" placeholder="Reference from bank">
                        </div>
                    </div>
                    <button class="fp-btn fp-btn-approve" onclick="freePaymentManager.confirmQris()">
                        <i class="fas fa-check"></i> Confirm Payment
                    </button>
                </div>
            </div>
        `;
    }

    async saveQrisConfig() {
        const mdrPercent = parseFloat(document.getElementById('fp-qris-mdr')?.value || '0.7');
        const data = {
            merchant_name: document.getElementById('fp-qris-merchant').value,
            qr_content: document.getElementById('fp-qris-content').value,
            acquirer_bank: document.getElementById('fp-qris-bank').value,
            mdr_rate: mdrPercent / 100,
            merchant_id: document.getElementById('fp-qris-mid').value || null,
            nmid: document.getElementById('fp-qris-nmid').value || null,
            is_active: true
        };

        if (!data.merchant_name || !data.qr_content) {
            alert('Merchant name and QR content are required');
            return;
        }

        try {
            await apiClient.saveQrisConfig(data);
            this.loadQrisConfig();
            if (typeof showToast === 'function') showToast('QRIS config saved', 'success');
        } catch (err) {
            alert('Failed: ' + err.message);
        }
    }

    showQrisGenerate() {
        const el = document.getElementById('fp-qris-generate');
        if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    async generateQris() {
        const amount = parseFloat(document.getElementById('fp-qris-amount').value);
        const orderId = document.getElementById('fp-qris-order').value || null;
        const resultEl = document.getElementById('fp-qris-result');

        if (!amount || amount <= 0) {
            alert('Amount is required');
            return;
        }

        try {
            const result = await apiClient.generateQrisPayment(amount, orderId);
            const d = result.data;
            resultEl.innerHTML = `
                <div class="fp-qris-result">
                    <div class="fp-card-row"><span>QR Content:</span></div>
                    <textarea readonly class="fp-textarea" rows="3">${d.qr_content}</textarea>
                    <div class="fp-card-row"><span>Amount:</span><strong>Rp ${this.formatRupiah(d.amount)}</strong></div>
                    <div class="fp-card-row"><span>MDR Fee:</span><strong>Rp ${this.formatRupiah(d.mdr_fee)}</strong></div>
                    <div class="fp-card-row"><span>Net Amount:</span><strong>Rp ${this.formatRupiah(d.net_amount)}</strong></div>
                    <p class="fp-help">Show this QR string to customer. They scan and pay the exact amount.</p>
                </div>
            `;
        } catch (err) {
            resultEl.innerHTML = `<div class="fp-error">${err.message}</div>`;
        }
    }

    editQrisConfig() {
        const c = this.qrisConfig;
        document.getElementById('fp-qris-config').innerHTML = this.renderQrisSetupForm();
        document.getElementById('fp-qris-merchant').value = c.merchant_name || '';
        document.getElementById('fp-qris-content').value = c.qr_content || '';
        document.getElementById('fp-qris-bank').value = c.acquirer_bank || '';
        document.getElementById('fp-qris-mdr').value = (c.mdr_rate * 100).toFixed(2);
        if (c.merchant_id) document.getElementById('fp-qris-mid').value = c.merchant_id;
        if (c.nmid) document.getElementById('fp-qris-nmid').value = c.nmid;
    }

    async confirmQris() {
        const paymentId = parseInt(document.getElementById('fp-qris-confirm-pid').value);
        const ref = document.getElementById('fp-qris-confirm-ref').value || null;

        if (!paymentId) {
            alert('Payment ID is required');
            return;
        }

        if (!confirm('Confirm this QRIS payment as completed?')) return;

        try {
            await apiClient.confirmQrisPayment(paymentId, ref);
            if (typeof showToast === 'function') showToast('QRIS payment confirmed', 'success');
            document.getElementById('fp-qris-confirm-pid').value = '';
            document.getElementById('fp-qris-confirm-ref').value = '';
        } catch (err) {
            alert('Failed: ' + err.message);
        }
    }

    // ===================================================================
    // TAB 3: WALLET TOP-UP
    // ===================================================================

    renderWalletTab() {
        return `
            <div class="fp-tab-content">
                <div class="fp-toolbar">
                    <select id="fp-topup-filter" onchange="freePaymentManager.loadTopupRequests()" class="fp-select">
                        <option value="pending" selected>Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button class="fp-btn fp-btn-refresh" onclick="freePaymentManager.loadTopupRequests()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
                <div id="fp-topup-list" class="fp-list">
                    <div class="fp-loading">Loading...</div>
                </div>
            </div>
        `;
    }

    async loadTopupRequests() {
        const filter = document.getElementById('fp-topup-filter')?.value || 'pending';
        const listEl = document.getElementById('fp-topup-list');
        if (!listEl) return;

        listEl.innerHTML = '<div class="fp-loading">Loading...</div>';

        try {
            const result = await apiClient.getTopupRequests(filter, { limit: 50 });
            this.topupRequests = result.data?.data || [];
            this.updateBadges();

            if (this.topupRequests.length === 0) {
                listEl.innerHTML = '<div class="fp-empty">No top-up requests found</div>';
                return;
            }

            listEl.innerHTML = this.topupRequests.map(t => this.renderTopupCard(t)).join('');
        } catch (err) {
            listEl.innerHTML = `<div class="fp-error">Failed to load: ${err.message}</div>`;
        }
    }

    renderTopupCard(topup) {
        const statusClass = `fp-status-${topup.status}`;
        const date = new Date(topup.created_at).toLocaleString('id-ID');
        const expires = topup.expires_at ? new Date(topup.expires_at).toLocaleString('id-ID') : null;

        let actions = '';
        if (topup.status === 'pending') {
            actions = `
                <div class="fp-card-actions">
                    <button class="fp-btn fp-btn-approve" onclick="freePaymentManager.verifyTopup(${topup.topup_id}, 'approve')">
                        <i class="fas fa-check"></i> Approve & Credit Wallet
                    </button>
                    <button class="fp-btn fp-btn-reject" onclick="freePaymentManager.verifyTopup(${topup.topup_id}, 'reject')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            `;
        }

        return `
            <div class="fp-card ${statusClass}">
                <div class="fp-card-header">
                    <div class="fp-card-title">
                        <strong>Top-up #${topup.topup_id}</strong>
                        <span class="fp-status-badge ${statusClass}">${topup.status}</span>
                    </div>
                    <div class="fp-card-date">${date}</div>
                </div>
                <div class="fp-card-body">
                    <div class="fp-card-row">
                        <span>Wallet:</span>
                        <strong>${topup.wallet_number}</strong>
                    </div>
                    <div class="fp-card-row">
                        <span>Customer ID:</span>
                        <strong>#${topup.customer_id}</strong>
                    </div>
                    <div class="fp-card-row">
                        <span>Amount:</span>
                        <strong class="fp-amount">Rp ${this.formatRupiah(topup.amount)}</strong>
                    </div>
                    <div class="fp-card-row">
                        <span>Current Balance:</span>
                        <strong>Rp ${this.formatRupiah(topup.current_balance)}</strong>
                    </div>
                    ${topup.bank_from ? `<div class="fp-card-row"><span>Bank:</span><strong>${topup.bank_from}</strong></div>` : ''}
                    ${topup.reference_number ? `<div class="fp-card-row"><span>Ref:</span><strong>${topup.reference_number}</strong></div>` : ''}
                    ${expires ? `<div class="fp-card-row"><span>Expires:</span><strong>${expires}</strong></div>` : ''}
                </div>
                ${topup.proof_file_path ? `
                    <div class="fp-card-footer">
                        <a href="/${topup.proof_file_path}" target="_blank" class="fp-btn fp-btn-view">
                            <i class="fas fa-eye"></i> View Proof
                        </a>
                    </div>
                ` : ''}
                ${actions}
            </div>
        `;
    }

    async verifyTopup(topupId, action) {
        const reason = action === 'reject'
            ? prompt('Reason for rejection:') || 'Top-up could not be verified'
            : null;

        if (action === 'approve' && !confirm('Approve this top-up? Wallet will be credited.')) return;

        try {
            const result = await apiClient.verifyWalletTopup(topupId, action, reason);
            this.loadTopupRequests();
            if (typeof showToast === 'function') {
                showToast(action === 'approve' ? `Top-up approved! New balance: Rp ${this.formatRupiah(result.data.new_balance)}` : 'Top-up rejected', 'success');
            }
        } catch (err) {
            if (typeof showToast === 'function') {
                showToast('Failed: ' + err.message, 'error');
            }
        }
    }

    // ===================================================================
    // HELPERS
    // ===================================================================

    updateBadges() {
        const pendingProofs = this.transferProofs.filter(p => p.verification_status === 'pending').length;
        const pendingTopups = this.topupRequests.filter(t => t.status === 'pending').length;

        const proofBadge = document.getElementById('fp-transfer-badge');
        const topupBadge = document.getElementById('fp-topup-badge');

        if (proofBadge) {
            proofBadge.textContent = pendingProofs;
            proofBadge.style.display = pendingProofs > 0 ? 'inline' : 'none';
        }
        if (topupBadge) {
            topupBadge.textContent = pendingTopups;
            topupBadge.style.display = pendingTopups > 0 ? 'inline' : 'none';
        }
    }

    formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID').format(amount || 0);
    }
}

// Initialize global instance
window.freePaymentManager = new FreePaymentManager();
