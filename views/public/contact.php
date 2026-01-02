<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - School Management System</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 100px 0;
        }
        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: var(--primary-color);
            border: none;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        .contact-info {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .contact-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-school"></i> School Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="/courses">Courses</a></li>
                    <li class="nav-item"><a class="nav-link" href="/events">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="/gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1>Contact Us</h1>
            <p class="lead">Get in touch with us for admissions, inquiries, or any questions</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-body p-4">
                            <h3 class="mb-4">Send us a Message</h3>

                            <?php if (isset($_SESSION['flash']['success'])): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['flash']['error'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['flash']['error']; unset($_SESSION['flash']['error']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['flash']['errors'])): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Please correct the errors below.
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="/contact">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? ''; ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="<?php echo $_SESSION['flash']['old']['name'] ?? ''; ?>" required>
                                        <?php if (isset($_SESSION['flash']['errors']['name'])): ?>
                                            <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['name'][0]; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?php echo $_SESSION['flash']['old']['email'] ?? ''; ?>" required>
                                        <?php if (isset($_SESSION['flash']['errors']['email'])): ?>
                                            <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['email'][0]; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject"
                                           value="<?php echo $_SESSION['flash']['old']['subject'] ?? ''; ?>" required>
                                    <?php if (isset($_SESSION['flash']['errors']['subject'])): ?>
                                        <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['subject'][0]; ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo $_SESSION['flash']['old']['message'] ?? ''; ?></textarea>
                                    <?php if (isset($_SESSION['flash']['errors']['message'])): ?>
                                        <div class="text-danger small"><?php echo $_SESSION['flash']['errors']['message'][0]; ?></div>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </form>

                            <?php unset($_SESSION['flash']['old'], $_SESSION['flash']['errors']); ?>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-4 mb-4">
                    <div class="contact-info">
                        <h4 class="mb-4">Get In Touch</h4>

                        <div class="contact-item">
                            <div class="contact-icon bg-primary text-white">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Address</h6>
                                <p class="mb-0">123 School Street<br>City, State 12345</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon bg-success text-white">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Phone</h6>
                                <p class="mb-0">+1-234-567-8900</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-item">
                            <div class="contact-icon bg-info text-white">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Email</h6>
                                <p class="mb-0">info@school.com</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon bg-warning text-white">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Office Hours</h6>
                                <p class="mb-0">Mon-Fri: 8:00 AM - 5:00 PM<br>Sat: 9:00 AM - 1:00 PM</p>
                            </div>
                        </div>
                    </div>

                    <!-- Map Placeholder -->
                    <div class="card mt-4">
                        <div class="card-body p-0">
                            <div style="height: 250px; background: #e9ecef; display: flex; align-items: center; justify-content: center;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                                    <p>Interactive Map<br><small>Location: 123 School Street, City, State</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admission Inquiry -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3>Interested in Admission?</h3>
                    <p class="lead">Contact our admissions office for detailed information about enrollment procedures, requirements, and available programs.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Admission Requirements:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Birth Certificate</li>
                                <li><i class="fas fa-check text-success me-2"></i>Previous School Records</li>
                                <li><i class="fas fa-check text-success me-2"></i>Medical Certificate</li>
                                <li><i class="fas fa-check text-success me-2"></i>Parent ID Proof</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Important Dates:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-calendar text-primary me-2"></i>Application Deadline: March 31</li>
                                <li><i class="fas fa-calendar text-primary me-2"></i>Entrance Test: April 15</li>
                                <li><i class="fas fa-calendar text-primary me-2"></i>Results: April 30</li>
                                <li><i class="fas fa-calendar text-primary me-2"></i>Admission Starts: May 1</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="card">
                        <div class="card-body">
                            <i class="fas fa-graduation-cap fa-4x text-primary mb-3"></i>
                            <h5>Apply Now</h5>
                            <p>Start your educational journey with us</p>
                            <a href="mailto:admissions@school.com" class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Email Admissions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>School Management System</h5>
                    <p>Providing quality education for over 50 years.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-white">Home</a></li>
                        <li><a href="/about" class="text-white">About Us</a></li>
                        <li><a href="/courses" class="text-white">Courses</a></li>
                        <li><a href="/events" class="text-white">Events</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-map-marker-alt"></i> 123 School Street, City, State</p>
                    <p><i class="fas fa-phone"></i> +1-234-567-8900</p>
                    <p><i class="fas fa-envelope"></i> info@school.com</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2024 School Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>