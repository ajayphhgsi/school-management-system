<?php
/**
 * Admin Homepage Controller
 */

class HomepageController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function homepage() {
        // Get homepage content by sections
        $carousel = $this->db->select("SELECT * FROM homepage_content WHERE section = 'carousel' AND is_active = 1 ORDER BY sort_order");
        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' AND is_active = 1 LIMIT 1");
        $courses = $this->db->select("SELECT * FROM homepage_content WHERE section = 'courses' AND is_active = 1 ORDER BY sort_order");
        $events = $this->db->select("SELECT * FROM events WHERE is_active = 1 ORDER BY event_date DESC LIMIT 5");
        $gallery = $this->db->select("SELECT * FROM gallery WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8");
        $testimonials = $this->db->select("SELECT * FROM homepage_content WHERE section = 'testimonials' AND is_active = 1 ORDER BY sort_order");

        // Get contact info from settings
        $contact = [
            'address' => $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_address'")['setting_value'] ?? '',
            'phone' => $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_phone'")['setting_value'] ?? '',
            'email' => $this->db->selectOne("SELECT setting_value FROM settings WHERE setting_key = 'school_email'")['setting_value'] ?? ''
        ];

        $this->render('admin/homepage/index', [
            'carousel' => $carousel,
            'about' => $about,
            'courses' => $courses,
            'events' => $events,
            'gallery' => $gallery,
            'testimonials' => $testimonials,
            'contact' => $contact
        ]);
    }

    public function homepageCarousel() {
        $carousel = $this->db->select("SELECT * FROM homepage_content WHERE section = 'carousel' ORDER BY sort_order");
        $csrfToken = $this->csrfToken();
        $this->render('admin/homepage/carousel', ['carousel' => $carousel, 'csrf_token' => $csrfToken]);
    }

    public function saveHomepageCarousel() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/homepage/carousel');
        }

        // Handle carousel updates
        if (isset($data['carousel'])) {
            foreach ($data['carousel'] as $id => $item) {
                $this->db->update('homepage_content', [
                    'title' => $item['title'] ?? '',
                    'content' => $item['content'] ?? '',
                    'link' => $item['link'] ?? '',
                    'sort_order' => $item['sort_order'] ?? 0,
                    'is_active' => isset($item['is_active']) ? 1 : 0
                ], 'id = ?', [$id]);
            }
        }

        // Handle new carousel item
        if (!empty($data['new_title'])) {
            $imagePath = '';
            if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOADS_PATH . 'homepage/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['new_image']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
                    $imagePath = 'homepage/' . $fileName;
                }
            }

            if ($imagePath) {
                $this->db->insert('homepage_content', [
                    'section' => 'carousel',
                    'title' => $data['new_title'],
                    'content' => $data['new_content'] ?? '',
                    'image_path' => $imagePath,
                    'link' => $data['new_link'] ?? '',
                    'sort_order' => $data['new_sort_order'] ?? 0,
                    'is_active' => 1
                ]);
            }
        }

        $this->session->setFlash('success', 'Carousel updated successfully');
        $this->redirect('/admin/homepage/carousel');
    }

    public function homepageAbout() {
        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' LIMIT 1");
        $csrfToken = $this->csrfToken();
        $this->render('admin/homepage/about', ['about' => $about, 'csrf_token' => $csrfToken]);
    }

    public function saveHomepageAbout() {
        $data = $_POST;
        $csrfToken = $data['csrf_token'] ?? '';

        if (!$this->checkCsrfToken($csrfToken)) {
            $this->session->setFlash('error', 'Invalid CSRF token');
            $this->redirect('/admin/homepage/about');
        }

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOADS_PATH . 'homepage/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'homepage/' . $fileName;
            }
        }

        $about = $this->db->selectOne("SELECT * FROM homepage_content WHERE section = 'about' LIMIT 1");

        if ($about) {
            // Update existing
            $updateData = [
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];
            if ($imagePath) {
                $updateData['image_path'] = $imagePath;
            }
            $this->db->update('homepage_content', $updateData, 'id = ?', [$about['id']]);
        } else {
            // Create new
            $this->db->insert('homepage_content', [
                'section' => 'about',
                'title' => $data['title'] ?? '',
                'content' => $data['content'] ?? '',
                'image_path' => $imagePath,
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]);
        }

        $this->session->setFlash('success', 'About section updated successfully');
        $this->redirect('/admin/homepage/about');
    }
}