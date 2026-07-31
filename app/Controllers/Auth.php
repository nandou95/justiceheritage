<?php

namespace App\Controllers;

use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use App\Models\PersonneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use App\Models\SexeModel;
use Modules\CourtJurisdiction\Models\ZoneModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DateTimeImmutable;
use Throwable;

class Auth extends BaseController
{
    private const MIN_AGE_YEARS = 16;
    private const CNI_MAX_KB    = 2048;
    private const CNI_EXTS      = 'pdf,jpg,jpeg,png';
    private const CNI_MIMES     = 'application/pdf,image/jpeg,image/png,image/jpg';

    public function register(): string
    {
        return view('public/register', $this->registerViewData(service('validation')));
    }

    public function registerSubmit()
    {
        $rules = [
            'first_name'       => 'required|min_length[2]|max_length[100]',
            'last_name'        => 'required|min_length[2]|max_length[100]',
            'date_of_birth'    => 'required|valid_date[Y-m-d]',
            'gender'           => 'required|is_natural_no_zero',
            'national_id'      => 'required|min_length[5]|max_length[50]',
            'national_id_file' => 'uploaded[national_id_file]'
                . '|max_size[national_id_file,' . self::CNI_MAX_KB . ']'
                . '|ext_in[national_id_file,' . self::CNI_EXTS . ']'
                . '|mime_in[national_id_file,' . self::CNI_MIMES . ']',
            'phone'            => 'required|min_length[8]|max_length[20]',
            'email'            => 'required|valid_email|max_length[150]',
            'address'          => 'required|min_length[5]|max_length[1000]',
            'birth_province'   => 'required|is_natural_no_zero',
            'birth_commune'    => 'required|is_natural_no_zero',
            'birth_zone'       => 'required|is_natural_no_zero',
            'birth_colline'    => 'required|is_natural_no_zero',
            'username'         => 'required|min_length[3]|max_length[100]',
            'password'         => 'required|min_length[8]|max_length[72]',
            'password_confirm' => 'required|matches[password]',
            'consent'          => 'required',
        ];

        $messages = [
            'national_id_file' => [
                'uploaded' => lang('Site.err_cni_required'),
                'max_size' => lang('Site.err_cni_size'),
                'ext_in'   => lang('Site.err_cni_type'),
                'mime_in'  => lang('Site.err_cni_type'),
            ],
            'date_of_birth' => [
                'required'   => lang('Site.err_dob'),
                'valid_date' => lang('Site.err_dob'),
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return $this->registrationFailed($this->validator->getErrors());
        }

        if (! $this->validateMinimumAge((string) $this->request->getPost('date_of_birth'))) {
            return $this->registrationFailed([
                'date_of_birth' => lang('Site.err_min_age'),
            ]);
        }

        if (! $this->validateRegisterLookups()) {
            return $this->registrationFailed($this->validator->getErrors());
        }

        $personneModel = new PersonneModel();
        $username      = trim((string) $this->request->getPost('username'));
        $email         = trim((string) $this->request->getPost('email'));
        $plainPassword = (string) $this->request->getPost('password');

        if ($personneModel->usernameExists($username)) {
            return $this->registrationFailed([
                'username' => lang('Site.err_username_taken'),
            ]);
        }

        if ($personneModel->emailExists($email)) {
            return $this->registrationFailed([
                'email' => lang('Site.err_email_taken'),
            ]);
        }

        $uploadPath = null;
        $firstName  = trim((string) $this->request->getPost('first_name'));
        $lastName   = trim((string) $this->request->getPost('last_name'));
        $db         = db_connect();
        $db->transStart();

        try {
            $uploadPath = $this->storeNationalIdFile();
            if ($uploadPath === null) {
                $db->transRollback();

                return $this->registrationFailed([
                    'national_id_file' => lang('Site.err_cni_save'),
                ]);
            }

            $inserted = $personneModel->insert([
                'nom_personne'          => $lastName,
                'prenom_personne'       => $firstName,
                'date_naissance'        => (string) $this->request->getPost('date_of_birth'),
                'sexe_id'               => (int) $this->request->getPost('gender'),
                'numero_cni'            => trim((string) $this->request->getPost('national_id')),
                'upload_cni'            => $uploadPath,
                'telephone'             => trim((string) $this->request->getPost('phone')),
                'email'                 => $email,
                'adresse_residence'     => trim((string) $this->request->getPost('address')),
                'province_naissance_id' => (int) $this->request->getPost('birth_province'),
                'commune_naissance_id'  => (int) $this->request->getPost('birth_commune'),
                'zone_naissance_id'     => (int) $this->request->getPost('birth_zone'),
                'colline_naissance_id'  => (int) $this->request->getPost('birth_colline'),
                'user_name'             => $username,
                'mot_de_passe_hash'     => password_hash($plainPassword, PASSWORD_DEFAULT),
                'code_authentification' => null,
                'code_authentification_expire_at' => null,
            ], true);

            if ($inserted === false) {
                throw new DatabaseException('Failed to insert plaignant.personne record.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Registration transaction failed.');
            }
        } catch (Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }

            if (is_string($uploadPath) && $uploadPath !== '') {
                $this->deleteUploadedFile($uploadPath);
            }

            log_message('error', 'Complainant registration DB failure: {message}', [
                'message' => $e->getMessage(),
            ]);

            return $this->registrationFailed([
                'database' => lang('Site.err_registration_save'),
            ]);
        }

        $fullName = trim($firstName . ' ' . $lastName);
        $mailer   = service('notifications');
        $mailOk   = $mailer->sendAccountRegistration(
            $email,
            $fullName,
            $username,
            $plainPassword,
            site_url('login')
        );

        if (! $mailOk) {
            log_message('error', 'Welcome email failed after successful registration for user {username} ({email}): {error}', [
                'username' => $username,
                'email'    => $email,
                'error'    => $mailer->getLastError(),
            ]);
        }

        return view('public/register_success', [
            'title'     => lang('Site.success_title'),
            'active'    => 'register',
            'name'      => $fullName,
            'message'   => lang('Site.success_completed'),
            'mailOk'    => $mailOk,
            'mailError' => $mailOk ? null : $mailer->getLastError(),
        ]);
    }

    public function login()
    {
        $auth = new \App\Libraries\ComplainantAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to('/portal');
        }

        return view('public/login', [
            'title'           => lang('Site.login_title'),
            'metaDescription' => lang('Site.login_meta'),
            'active'          => 'login',
        ]);
    }

    public function loginSubmit()
    {
        $auth = new \App\Libraries\ComplainantAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to('/portal');
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $result   = $auth->beginLogin($username, $password);

        if (! $result['ok']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['error'] ?? lang('Site.login_err_credentials'));
        }

        return redirect()->to('/login/2fa')
            ->with('email_masked', $result['email_masked'] ?? '');
    }

    public function loginTwoFactor()
    {
        $auth = new \App\Libraries\ComplainantAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to('/portal');
        }

        if (! $auth->hasPendingChallenge()) {
            return redirect()->to('/login')->with('error', lang('Site.login_err_session'));
        }

        $pendingId = (int) session()->get(\App\Libraries\ComplainantAuth::SESSION_PENDING_ID);
        if ($pendingId > 0) {
            (new \App\Models\PersonneModel())->purgeExpiredAuthenticationCode($pendingId);
        }

        return view('public/login_2fa', [
            'title'           => lang('Site.login_2fa_title'),
            'metaDescription' => lang('Site.login_meta'),
            'active'          => 'login',
            'emailMasked'     => session()->getFlashdata('email_masked')
                ?? session()->get(\App\Libraries\ComplainantAuth::SESSION_EMAIL_MASKED)
                ?? '***',
            'expiresIn'       => max(0, (int) session()->get(\App\Libraries\ComplainantAuth::SESSION_EXPIRES_AT) - time()),
        ]);
    }

    public function loginTwoFactorSubmit()
    {
        $auth = new \App\Libraries\ComplainantAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to('/portal');
        }

        $result = $auth->verifyCode((string) $this->request->getPost('code'));
        if ($result['ok']) {
            return redirect()->to('/portal');
        }

        // Session ended (max attempts / missing challenge): restart from password step.
        if (! empty($result['expired']) && ! $auth->hasPendingChallenge()) {
            return redirect()->to('/login')->with('error', $result['error'] ?? lang('Site.login_err_session'));
        }

        return redirect()->back()->with('error', $result['error'] ?? lang('Site.login_err_code_incorrect'));
    }

    public function loginTwoFactorResend()
    {
        $auth = new \App\Libraries\ComplainantAuth();
        if ($auth->isAuthenticated()) {
            return redirect()->to('/portal');
        }

        $result = $auth->resendCode();
        if (! $result['ok']) {
            return redirect()->to('/login')->with('error', $result['error'] ?? lang('Site.login_err_code_send'));
        }

        return redirect()->to('/login/2fa')
            ->with('email_masked', $result['email_masked'] ?? '')
            ->with('success', lang('Site.login_code_sent'));
    }

    public function logout()
    {
        (new \App\Libraries\ComplainantAuth())->logout();

        return redirect()->to('/login');
    }

    /**
     * @param array<string, string> $errors
     */
    private function registrationFailed(array $errors)
    {
        return redirect()->back()
            ->withInput()
            ->with('errors', $errors);
    }

    private function validateMinimumAge(string $dateOfBirth): bool
    {
        try {
            $dob = new DateTimeImmutable($dateOfBirth);
        } catch (Throwable) {
            return false;
        }

        $today        = new DateTimeImmutable('today');
        $minBirthDate = $today->modify('-' . self::MIN_AGE_YEARS . ' years');

        return $dob <= $minBirthDate;
    }

    private function storeNationalIdFile(): ?string
    {
        $file = $this->request->getFile('national_id_file');
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            log_message('error', 'CNI upload invalid: {error}', [
                'error' => $file?->getErrorString() ?? 'missing file',
            ]);

            return null;
        }

        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'cni';
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0750, true) && ! is_dir($targetDir)) {
            log_message('error', 'Unable to create CNI upload directory: {dir}', ['dir' => $targetDir]);

            return null;
        }

        $newName = $file->getRandomName();
        if (! $file->move($targetDir, $newName)) {
            log_message('error', 'Unable to move CNI upload to {dir}', ['dir' => $targetDir]);

            return null;
        }

        return 'uploads/cni/' . $newName;
    }

    private function deleteUploadedFile(string $relativePath): void
    {
        $fullPath = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function registerViewData($validation): array
    {
        $provinceId = (int) old('birth_province');
        $communeId  = (int) old('birth_commune');
        $zoneId     = (int) old('birth_zone');
        $maxDob     = (new DateTimeImmutable('today'))
            ->modify('-' . self::MIN_AGE_YEARS . ' years')
            ->format('Y-m-d');

        return [
            'title'           => lang('Site.register_title'),
            'metaDescription' => lang('Site.register_meta'),
            'active'          => 'register',
            'validation'      => $validation,
            'genders'         => (new SexeModel())->options(),
            'provinces'       => (new ProvinceModel())->options(),
            'communes'        => $provinceId > 0 ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'           => $communeId > 0 ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'        => $zoneId > 0 ? (new CollineModel())->optionsByZone($zoneId) : [],
            'maxDob'          => $maxDob,
            'cniMaxMb'        => self::CNI_MAX_KB / 1024,
        ];
    }

    private function validateRegisterLookups(): bool
    {
        $genderId   = (int) $this->request->getPost('gender');
        $provinceId = (int) $this->request->getPost('birth_province');
        $communeId  = (int) $this->request->getPost('birth_commune');
        $zoneId     = (int) $this->request->getPost('birth_zone');
        $collineId  = (int) $this->request->getPost('birth_colline');

        $sexeOk     = (new SexeModel())->find($genderId) !== null;
        $provinceOk = (new ProvinceModel())->find($provinceId) !== null;

        $communeOk = false;
        if ($communeId > 0 && $provinceId > 0) {
            $commune   = (new CommuneModel())->find($communeId);
            $communeOk = is_array($commune) && (int) $commune['province_id'] === $provinceId;
        }

        $zoneOk = false;
        if ($zoneId > 0 && $communeId > 0) {
            $zone   = (new ZoneModel())->find($zoneId);
            $zoneOk = is_array($zone) && (int) $zone['commune_id'] === $communeId;
        }

        $collineOk = false;
        if ($collineId > 0 && $zoneId > 0) {
            $colline   = (new CollineModel())->find($collineId);
            $collineOk = is_array($colline) && (int) $colline['zone_id'] === $zoneId;
        }

        if ($sexeOk && $provinceOk && $communeOk && $zoneOk && $collineOk) {
            return true;
        }

        if (! $sexeOk) {
            $this->validator->setError('gender', lang('Site.err_gender'));
        }
        if (! $provinceOk) {
            $this->validator->setError('birth_province', lang('Site.err_birth_province'));
        }
        if (! $communeOk) {
            $this->validator->setError('birth_commune', lang('Site.err_birth_commune'));
        }
        if (! $zoneOk) {
            $this->validator->setError('birth_zone', lang('Site.err_birth_zone'));
        }
        if (! $collineOk) {
            $this->validator->setError('birth_colline', lang('Site.err_birth_colline'));
        }

        return false;
    }
}

