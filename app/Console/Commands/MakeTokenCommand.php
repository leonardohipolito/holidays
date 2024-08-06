<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

class MakeTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new token for the user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = text(
            label: 'Enter the email address of the user',
            required: true,
            default: 'test@example.com',
            validate: [
                'email' => 'exists:users,email',
            ]
        );
        $user = User::firstWhere('email', $email);
        $token = $user->createToken('api-token', [
            'holiday:viewAny',
            'holiday:view',
            'holiday:create',
            'holiday:update',
            'holiday:delete',
        ])->plainTextToken;
        info('Token generated: '.$token);
    }
}
