<?php
// public/contact.php
require_once __DIR__ . '/../src/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Send email to admin
    $adminEmail = "billonyoni@gmail.com";
    $emailSubject = "Contact Form: " . $subject;
    $emailMessage = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    
    if (sendEmail($adminEmail, $emailSubject, $emailMessage)) {
        $success = "Thank you for your message. We'll get back to you soon!";
    } else {
        $error = "Failed to send message. Please try again.";
    }
}
?>

<div class="row">
    <div class="col-md-8">
        <h2>Contact Us</h2>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Subject</label>
                <input name="subject" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control" rows="5" required></textarea>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit">Send Message</button>
            </div>
        </form>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Get in Touch</h5>
            </div>
            <div class="card-body">
                <p><i class="fas fa-map-marker-alt text-primary"></i> Moi Avenue, Nairobi<br><small class="text-muted">Next to MKU Towers</small></p>
                <p><i class="fas fa-phone text-success"></i> +254717728990</p>
                <p><i class="fas fa-envelope text-info"></i> billonyoni@gmail.com</p>
                
                <hr>
                
                <h6>Business Hours</h6>
                <p class="small">
                    Monday - Friday: 8:00 AM - 6:00 PM<br>
                    Saturday: 9:00 AM - 5:00 PM<br>
                    Sunday: Closed
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>