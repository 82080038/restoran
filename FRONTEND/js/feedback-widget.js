/**
 * F&B Management System Feedback Widget
 * Floating action button for customers to submit reviews and feedback
 */
class FeedbackWidget {
    constructor() {
        this.isOpen = false;
        this.selectedType = 'suggestion';
        this.selectedRating = 0;
        this.init();
    }

    init() {
        this.injectStyles();
        this.render();
        this.attachEvents();
    }

    injectStyles() {
        if (document.getElementById('feedback-widget-styles')) return;
        const link = document.createElement('link');
        link.id = 'feedback-widget-styles';
        link.rel = 'stylesheet';
        link.href = '../css/i18n-feedback.css';
        document.head.appendChild(link);
    }

    render() {
        const container = document.createElement('div');
        container.id = 'feedback-widget';
        container.innerHTML = `
            <button class="feedback-fab" id="feedback-fab" title="Give Feedback">
                <i class="fas fa-comment-dots"></i>
            </button>
            <div class="feedback-modal" id="feedback-modal">
                <div class="feedback-modal-header">
                    <h5><i class="fas fa-comment-dots"></i> Share Your Experience</h5>
                    <button type="button" style="background:none;border:none;color:white;font-size:18px;cursor:pointer" onclick="window.feedbackWidget.close()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="feedback-modal-body" id="feedback-body">
                    <div class="feedback-type-grid" id="feedback-types">
                        <div class="feedback-type-btn selected" data-type="suggestion">
                            <i class="fas fa-lightbulb"></i> Suggestion
                        </div>
                        <div class="feedback-type-btn" data-type="complaint">
                            <i class="fas fa-exclamation-circle"></i> Complaint
                        </div>
                        <div class="feedback-type-btn" data-type="compliment">
                            <i class="fas fa-heart"></i> Compliment
                        </div>
                        <div class="feedback-type-btn" data-type="question">
                            <i class="fas fa-question-circle"></i> Question
                        </div>
                    </div>

                    <div id="rating-section" style="margin-bottom:12px">
                        <label style="font-size:13px;color:#2c3e50;display:block;margin-bottom:6px">How would you rate your experience?</label>
                        <div class="star-rating" id="star-rating">
                            <i class="fas fa-star" data-rating="1"></i>
                            <i class="fas fa-star" data-rating="2"></i>
                            <i class="fas fa-star" data-rating="3"></i>
                            <i class="fas fa-star" data-rating="4"></i>
                            <i class="fas fa-star" data-rating="5"></i>
                        </div>
                    </div>

                    <input type="text" class="feedback-input" id="feedback-subject" placeholder="Subject">
                    <textarea class="feedback-textarea" id="feedback-message" placeholder="Tell us more..."></textarea>

                    <button class="feedback-submit-btn" id="feedback-submit" onclick="window.feedbackWidget.submit()">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
                <div class="feedback-modal-body" id="feedback-success" style="display:none">
                    <div class="feedback-success">
                        <i class="fas fa-check-circle"></i>
                        <h5>Thank You!</h5>
                        <p>Your feedback has been submitted. We appreciate your input!</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(container);
    }

    attachEvents() {
        document.getElementById('feedback-fab').addEventListener('click', () => this.toggle());

        document.querySelectorAll('.feedback-type-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.feedback-type-btn').forEach(b => b.classList.remove('selected'));
                e.currentTarget.classList.add('selected');
                this.selectedType = e.currentTarget.getAttribute('data-type');
            });
        });

        document.querySelectorAll('#star-rating i').forEach(star => {
            star.addEventListener('click', (e) => {
                const rating = parseInt(e.currentTarget.getAttribute('data-rating'));
                this.selectedRating = rating;
                this.updateStars(rating);
            });

            star.addEventListener('mouseenter', (e) => {
                const rating = parseInt(e.currentTarget.getAttribute('data-rating'));
                this.updateStars(rating);
            });
        });

        document.getElementById('star-rating').addEventListener('mouseleave', () => {
            this.updateStars(this.selectedRating);
        });
    }

    updateStars(rating) {
        document.querySelectorAll('#star-rating i').forEach(star => {
            const val = parseInt(star.getAttribute('data-rating'));
            star.classList.toggle('active', val <= rating);
        });
    }

    toggle() {
        this.isOpen ? this.close() : this.open();
    }

    open() {
        document.getElementById('feedback-modal').classList.add('show');
        this.isOpen = true;
    }

    close() {
        document.getElementById('feedback-modal').classList.remove('show');
        this.isOpen = false;
    }

    async submit() {
        const subject = document.getElementById('feedback-subject').value.trim();
        const message = document.getElementById('feedback-message').value.trim();

        if (!subject || !message) {
            alert('Please fill in subject and message');
            return;
        }

        const btn = document.getElementById('feedback-submit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

        const apiBase = window.API_BASE_URL || '/api/v1';
        const body = {
            feedback_type: this.selectedType,
            subject: subject,
            message: message,
            tenant_id: parseInt(localStorage.getItem('tenantId') || '1')
        };

        // If rating > 0, submit as review too
        if (this.selectedRating > 0) {
            body.rating = this.selectedRating;
        }

        try {
            // Submit feedback
            const resp = await fetch(`${apiBase}/feedback`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await resp.json();

            if (data.success) {
                // If rating was given, also submit a review
                if (this.selectedRating > 0) {
                    await fetch(`${apiBase}/feedback/reviews`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            tenant_id: body.tenant_id,
                            rating: this.selectedRating,
                            title: subject,
                            comment: message,
                            source: 'widget'
                        })
                    });
                }

                // Show success
                document.getElementById('feedback-body').style.display = 'none';
                document.getElementById('feedback-success').style.display = 'block';

                setTimeout(() => {
                    this.reset();
                    this.close();
                }, 2500);
            } else {
                alert(data.message || 'Failed to submit feedback');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit';
            }
        } catch (err) {
            alert('Connection error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit';
        }
    }

    reset() {
        document.getElementById('feedback-body').style.display = 'block';
        document.getElementById('feedback-success').style.display = 'none';
        document.getElementById('feedback-subject').value = '';
        document.getElementById('feedback-message').value = '';
        this.selectedRating = 0;
        this.updateStars(0);
        const btn = document.getElementById('feedback-submit');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit';
    }
}

// Initialize feedback widget on page load
window.feedbackWidget = new FeedbackWidget();
