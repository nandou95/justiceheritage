<?php

namespace Modules\Administration\Controllers;

use App\Models\SexeModel;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Administration\Models\JuridictionModel;
use Modules\Administration\Models\NiveauJuridictionModel;
use Modules\Administration\Models\ProfilModel;
use Modules\Administration\Services\UserService;
use Modules\CourtJurisdiction\Models\CollineModel;
use Modules\CourtJurisdiction\Models\CommuneModel;
use Modules\CourtJurisdiction\Models\ProvinceModel;
use Modules\CourtJurisdiction\Models\ZoneModel;

class Users extends \App\Controllers\BaseController
{
    private UserService $users;

    public function __construct()
    {
        $this->users = new UserService();
    }

    public function index()
    {
        $filters = [
            'province_id'           => $this->request->getGet('province_id'),
            'commune_id'            => $this->request->getGet('commune_id'),
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id'),
            'juridiction_id'        => $this->request->getGet('juridiction_id'),
            'account_status'        => $this->request->getGet('account_status'),
        ];

        $provinceId = (int) ($filters['province_id'] ?? 0);
        $niveauId   = (int) ($filters['niveau_juridiction_id'] ?? 0);

        return view('Modules\Administration\Views\users\index', [
            'title'          => lang('Backoffice.users_title'),
            'active'         => 'users',
            'users'          => $this->users->listUsers($filters),
            'filters'        => $filters,
            'provinces'      => (new ProvinceModel())->options(),
            'communes'       => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'niveaux'        => (new NiveauJuridictionModel())->options(),
            'jurisdictions'  => (new JuridictionModel())->options([
                'niveau_juridiction_id' => $niveauId ?: null,
                'province_id'           => $provinceId ?: null,
                'commune_id'            => ! empty($filters['commune_id']) ? (int) $filters['commune_id'] : null,
            ]),
            'user'           => $this->sampleUser(),
        ]);
    }

    public function create()
    {
        $provinceId = (int) (old('province_naissance_id') ?: 0);
        $communeId  = (int) (old('commune_naissance_id') ?: 0);
        $zoneId     = (int) (old('zone_naissance_id') ?: 0);

        return view('Modules\Administration\Views\users\form', [
            'title'         => lang('Backoffice.users_create_title'),
            'active'        => 'users',
            'mode'          => 'create',
            'record'        => $this->emptyRecord(),
            'profiles'      => (new ProfilModel())->options(),
            'jurisdictions' => (new JuridictionModel())->options(),
            'sexes'         => (new SexeModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'         => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'      => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'user'          => $this->sampleUser(),
        ]);
    }

    public function store()
    {
        $input  = $this->request->getPost();
        $result = $this->users->create($input);

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.users_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/users'))
            ->with('success', lang('Backoffice.users_created'));
    }

    public function edit(int $id)
    {
        $record = $this->users->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/users'))->with('error', lang('Backoffice.users_err_not_found'));
        }

        $provinceId = (int) (old('province_naissance_id') ?: ($record['province_naissance_id'] ?? 0));
        $communeId  = (int) (old('commune_naissance_id') ?: ($record['commune_naissance_id'] ?? 0));
        $zoneId     = (int) (old('zone_naissance_id') ?: ($record['zone_naissance_id'] ?? 0));

        return view('Modules\Administration\Views\users\form', [
            'title'         => lang('Backoffice.users_edit_title'),
            'active'        => 'users',
            'mode'          => 'edit',
            'record'        => $record,
            'profiles'      => (new ProfilModel())->options(),
            'jurisdictions' => (new JuridictionModel())->options(),
            'sexes'         => (new SexeModel())->options(),
            'provinces'     => (new ProvinceModel())->options(),
            'communes'      => $provinceId ? (new CommuneModel())->optionsByProvince($provinceId) : [],
            'zones'         => $communeId ? (new ZoneModel())->optionsByCommune($communeId) : [],
            'collines'      => $zoneId ? (new CollineModel())->optionsByZone($zoneId) : [],
            'user'          => $this->sampleUser(),
        ]);
    }

    public function update(int $id)
    {
        $result = $this->users->update($id, $this->request->getPost());

        if (! ($result['ok'] ?? false)) {
            return redirect()->back()->withInput()->with('errors', $result['errors'] ?? [lang('Backoffice.users_err_save')]);
        }

        return redirect()
            ->to(site_url('backoffice/users'))
            ->with('success', lang('Backoffice.users_updated'));
    }

    public function show(int $id)
    {
        $record = $this->users->find($id);
        if (! $record) {
            return redirect()->to(site_url('backoffice/users'))->with('error', lang('Backoffice.users_err_not_found'));
        }

        return view('Modules\Administration\Views\users\show', [
            'title'  => lang('Backoffice.users_details_title'),
            'active' => 'users',
            'record' => $record,
            'user'   => $this->sampleUser(),
        ]);
    }

    public function toggleStatus(int $id)
    {
        $result = $this->users->toggleStatus($id);

        if (! ($result['ok'] ?? false)) {
            return redirect()->to(site_url('backoffice/users'))->with('error', ($result['errors'][0] ?? lang('Backoffice.users_err_save')));
        }

        $message = ($result['activated'] ?? false)
            ? lang('Backoffice.users_activated')
            : lang('Backoffice.users_deactivated');

        return redirect()->to(site_url('backoffice/users'))->with('success', $message);
    }

    public function jurisdictions(): ResponseInterface
    {
        $options = (new JuridictionModel())->options([
            'niveau_juridiction_id' => $this->request->getGet('niveau_juridiction_id') ? (int) $this->request->getGet('niveau_juridiction_id') : null,
            'province_id'           => $this->request->getGet('province_id') ? (int) $this->request->getGet('province_id') : null,
            'commune_id'            => $this->request->getGet('commune_id') ? (int) $this->request->getGet('commune_id') : null,
        ]);

        return $this->response->setJSON(['ok' => true, 'options' => $options]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRecord(): array
    {
        return [
            'utilisateur_id'        => null,
            'nom_utilisateur'       => old('nom_utilisateur'),
            'prenom_utilisateur'    => old('prenom_utilisateur'),
            'numero_cni'            => old('numero_cni'),
            'numero_matricule'       => old('numero_matricule'),
            'telephone'             => old('telephone'),
            'email'                 => old('email'),
            'date_naissance'        => old('date_naissance'),
            'profil_id'             => old('profil_id'),
            'juridiction_id'        => old('juridiction_id'),
            'sexe_id'               => old('sexe_id'),
            'province_naissance_id' => old('province_naissance_id'),
            'commune_naissance_id'  => old('commune_naissance_id'),
            'zone_naissance_id'     => old('zone_naissance_id'),
            'colline_naissance_id'  => old('colline_naissance_id'),
        ];
    }

    /**
     * @return array{name:string,role:string}
     */
    private function sampleUser(): array
    {
        return [
            'name' => lang('Backoffice.user_sample'),
            'role' => lang('Backoffice.role_sample'),
        ];
    }
}
