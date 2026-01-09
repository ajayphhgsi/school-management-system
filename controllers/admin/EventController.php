<?php
/**
 * Admin Event Controller
 */

class EventController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('admin');
    }

    private function getCurrentAcademicYearId() {
        return $_SESSION['academic_year_id'] ?? null;
    }

    public function events() {
        $academicYearId = $this->getCurrentAcademicYearId();
        $where = "";
        $params = [];
        if ($academicYearId) {
            $where = "WHERE academic_year_id = ?";
            $params = [$academicYearId];
        }
        $events = $this->db->select("SELECT * FROM events $where ORDER BY created_at DESC", $params);
        $this->render('admin/events/index', ['events' => $events]);
    }
}