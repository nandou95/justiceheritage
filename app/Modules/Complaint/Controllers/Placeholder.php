<?php

namespace Modules\Complaint\Controllers;

class Placeholder extends \App\Controllers\BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'module' => 'Complaint',
            'status' => 'scaffold',
        ]);
    }
}
