<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Services\ApplicationLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class OthersController extends Controller
{
    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            $data = [
                'subject' => 'Test Email',
                'body' => 'This is a test email to verify the mail configuration.',
            ];

            Mail::to($request->test_email)->send(new TestMail($data));

            return response()->json([
                'status' => 200,
                'message' => __('Test email sent successfully to ') . $request->test_email,
                'redirect' => null,
            ]);
        } catch (\Exception $e) {
            app(ApplicationLogger::class)->error('Admin test mail send failed', [
                'category' => 'admin',
                'source' => self::class,
                'recipient' => $request->test_email,
            ], $e);

            return response()->json([
                'status' => 500,
                'message' => __('Failed to send test email: ') . $e->getMessage(),
                'redirect' => null,
            ]);
        }
    }

    public function migrate()
    {
        $userId = Auth::user()->id ?? 1;
        Artisan::call('migrate:fresh --seed');
        Auth::loginUsingId($userId);
        notify()->success('Database migration has been completed successfully.');
        return redirect('/admin/dashboard');
    }

    public function clear()
    {
        Artisan::call('optimize:clear');
        notify()->success('Cache has been cleared successfully.');
        return redirect('/admin/dashboard');
    }

    public function composer()
    {
        exec('composer update');
        exec('composer dump-autoload');
        notify()->success('Composer update has been completed successfully.');
        return redirect('/admin/dashboard');
    }

    public function iseed()
    {
        $tables = DB::select('SHOW TABLES');
        $prevent_tables = ['failed_jobs', 'migrations', 'password_reset_tokens', 'personal_access_tokens', 'sessions', 'cache', 'cache_locks', 'failed_jobs', 'job_batches', 'jobs'];
        foreach ($tables as $table) {
            $table_name = 'Tables_in_' . env('DB_DATABASE');
            $table_name = $table->$table_name;
            if (!in_array($table_name, $prevent_tables))
                Artisan::call('iseed ' . $table_name . ' --force');
        }
        notify()->success('Database seed has been created successfully.');
        return redirect('/admin/dashboard');
    }
}
