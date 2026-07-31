<?php



namespace Modules\Notification\Commands;



use App\Models\PersonneModel;

use CodeIgniter\CLI\BaseCommand;

use CodeIgniter\CLI\CLI;

use Modules\Notification\Services\NotificationMailer;



/**

 * Verifies 2FA code persistence + email delivery without exposing the code.

 */

class TwoFactorSmoke extends BaseCommand

{

    protected $group       = 'Email';

    protected $name        = 'email:2fa-smoke';

    protected $description = 'Generate, store, and email a 2FA code for a complainant username.';

    protected $usage       = 'email:2fa-smoke <username>';

    protected $arguments   = [

        'username' => 'Complainant username (user_name).',

    ];



    public function run(array $params)

    {

        $username = trim((string) ($params[0] ?? ''));

        if ($username === '') {

            CLI::error('Usage: php spark email:2fa-smoke <username>');



            return EXIT_ERROR;

        }



        $pm     = new PersonneModel();

        $person = $pm->findByUsername($username);

        if ($person === null) {

            CLI::error('Complainant not found for username: ' . $username);



            return EXIT_ERROR;

        }



        $code      = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $expiresAt = time() + 60;



        CLI::write('personne_id : ' . $person['personne_id']);

        CLI::write('email       : ' . $person['email']);

        CLI::write('Saving authentication code…');



        if (! $pm->setAuthenticationCode((int) $person['personne_id'], $code, $expiresAt)) {

            CLI::error('Failed to save code_authentification.');



            return EXIT_ERROR;

        }



        $row = $pm->find((int) $person['personne_id']);

        $ok  = is_array($row)

            && hash_equals((string) ($row['code_authentification'] ?? ''), $code)

            && ! empty($row['code_authentification_expire_at']);

        CLI::write('DB save     : ' . ($ok ? 'OK' : 'FAIL'), $ok ? 'green' : 'red');

        if (! $ok) {

            return EXIT_ERROR;

        }



        /** @var NotificationMailer $mailer */

        $mailer = service('notifications');

        CLI::write('Sending 2FA email…');

        $sent = $mailer->sendTwoFactorCode(

            (string) $person['email'],

            trim($person['prenom_personne'] . ' ' . $person['nom_personne']),

            ['code' => $code, 'ttl' => 60]

        );



        if (! $sent) {

            $pm->clearAuthenticationCode((int) $person['personne_id']);

            CLI::error('Email failed: ' . $mailer->getLastError());



            return EXIT_ERROR;

        }



        CLI::write('2FA email sent successfully to ' . $person['email'], 'green');

        CLI::write('Temporary code left in DB for 60s so you can verify receipt. Clear with a successful login or wait for expiry.');



        return EXIT_SUCCESS;

    }

}

