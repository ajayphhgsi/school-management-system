<?php
/**
 * Public Controller - Public Website Pages
 */

class PublicController extends Controller {

    public function index() {
        // Get homepage content from database
        $carousel = $this->db->select("SELECT * FROM homepage_content WHERE section = 'carousel' AND is_active = 1 ORDER BY sort_order");
        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' AND is_active = 1");
        $courses = $this->db->select("SELECT * FROM homepage_content WHERE section = 'courses' AND is_active = 1 ORDER BY sort_order");
        $events = $this->db->select("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date DESC LIMIT 6");
        $gallery = $this->db->select("SELECT * FROM gallery WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8");
        $testimonials = $this->db->select("SELECT * FROM homepage_content WHERE section = 'testimonials' AND is_active = 1 ORDER BY sort_order");

        // Get settings for dynamic content
        $schoolName = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_name'")['setting_value'] ?? 'School Management System';
        $schoolAddress = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '';
        $schoolPhone = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '';
        $schoolEmail = $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? '';

        $data = [
            'carousel' => $carousel,
            'about' => $about,
            'courses' => $courses,
            'events' => $events,
            'gallery' => $gallery,
            'testimonials' => $testimonials,
            'school_name' => $schoolName,
            'school_address' => $schoolAddress,
            'school_phone' => $schoolPhone,
            'school_email' => $schoolEmail
        ];

        $this->render('public/homepage', $data);
    }

    public function about() {
        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' AND is_active = 1");
        $this->render('public/about', ['about' => $about]);
    }

    public function courses() {
        $courses = $this->db->select("SELECT * FROM homepage_content WHERE section = 'courses' AND is_active = 1 ORDER BY sort_order");
        $this->render('public/courses', ['courses' => $courses]);
    }

    public function events() {
        $events = $this->db->select("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date DESC");
        $this->render('public/events', ['events' => $events]);
    }

    public function gallery() {
        $gallery = $this->db->select("SELECT * FROM gallery WHERE is_active = 1 ORDER BY created_at DESC");
        $this->render('public/gallery', ['gallery' => $gallery]);
    }

    public function contact() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleContactForm();
        } else {
            $this->render('public/contact');
        }
    }

    private function handleContactForm() {
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? '',
            'csrf_token' => $_POST['csrf_token'] ?? ''
        ];

        if (!$this->checkCsrfToken($data['csrf_token'])) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->back();
        }

        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'subject' => 'required|min:5|max:200',
            'message' => 'required|min:10|max:1000'
        ];

        if (!$this->validate($data, $rules)) {
            $this->session->setFlash('errors', $this->getValidationErrors());
            $this->session->setFlash('old', $data);
            $this->back();
        }

        // Here you would typically send an email or save to database
        $this->session->setFlash('success', 'Thank you for your message. We will get back to you soon!');
        $this->redirect('/contact');
    }
}