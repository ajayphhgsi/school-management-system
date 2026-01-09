<?php
/**
 * Admin Gallery Controller
 */

class GalleryController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function gallery() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "WHERE is_active = 1";
        $params = [];

        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params = [$academicYearId];
        }

        $gallery = $this->db->select("SELECT * FROM gallery $where ORDER BY created_at DESC", $params);
        $this->render('admin/gallery/index', ['gallery' => $gallery]);
    }

    public function uploadGallery() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['title']) || !isset($data['image'])) {
            $this->json(['success' => false, 'message' => 'Title and image are required'], 400);
        }

        $academicYearId = $this->getCurrentAcademicYearId();

        // Decode base64 image
        $imageData = $data['image'];
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageType = $matches[1];
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $imageData = base64_decode($imageData);

            // Generate filename
            $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $imageType;
            $uploadPath = UPLOAD_PATH . 'gallery/' . $filename;

            // Ensure directory exists
            $uploadDir = dirname($uploadPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Save image
            if (file_put_contents($uploadPath, $imageData)) {
                // Save to database
                $galleryId = $this->db->insert('gallery', [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? '',
                    'image_path' => 'gallery/' . $filename,
                    'academic_year_id' => $academicYearId,
                    'is_active' => 1
                ]);

                // Log the action
                $this->db->insert('audit_logs', [
                    'user_id' => $_SESSION['user']['id'] ?? 1,
                    'action' => 'gallery_upload',
                    'table_name' => 'gallery',
                    'record_id' => $galleryId,
                    'new_values' => json_encode(['title' => $data['title'], 'image_path' => 'gallery/' . $filename])
                ]);

                $this->json(['success' => true, 'message' => 'Gallery image uploaded successfully', 'id' => $galleryId]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to save image file'], 500);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Invalid image format'], 400);
        }
    }

    public function deleteGallery() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['id'])) {
            $this->json(['success' => false, 'message' => 'Gallery ID is required'], 400);
        }

        $galleryId = $data['id'];

        // Get gallery record
        $gallery = $this->db->selectOne("SELECT * FROM gallery WHERE id = ?", [$galleryId]);
        if (!$gallery) {
            $this->json(['success' => false, 'message' => 'Gallery item not found'], 404);
        }

        // Delete physical file
        $filePath = UPLOAD_PATH . $gallery['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $this->db->delete('gallery', 'id = ?', [$galleryId]);

        // Log the action
        $this->db->insert('audit_logs', [
            'user_id' => $_SESSION['user']['id'] ?? 1,
            'action' => 'gallery_delete',
            'table_name' => 'gallery',
            'record_id' => $galleryId,
            'old_values' => json_encode(['title' => $gallery['title'], 'image_path' => $gallery['image_path']])
        ]);

        $this->json(['success' => true, 'message' => 'Gallery item deleted successfully']);
    }

    public function updateGallery() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['id']) || !isset($data['title'])) {
            $this->json(['success' => false, 'message' => 'ID and title are required'], 400);
        }

        $galleryId = $data['id'];

        // Update gallery
        $this->db->update('gallery', [
            'title' => $data['title'],
            'description' => $data['description'] ?? ''
        ], 'id = ?', [$galleryId]);

        // Log the action
        $this->db->insert('audit_logs', [
            'user_id' => $_SESSION['user']['id'] ?? 1,
            'action' => 'gallery_update',
            'table_name' => 'gallery',
            'record_id' => $galleryId,
            'new_values' => json_encode(['title' => $data['title'], 'description' => $data['description'] ?? ''])
        ]);

        $this->json(['success' => true, 'message' => 'Gallery item updated successfully']);
    }
}