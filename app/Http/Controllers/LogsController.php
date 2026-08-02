<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logPath)) {
            $content = File::get($logPath);
            
            // Matches: [2026-06-20 18:55:00] local.ERROR: Message
            preg_match_all("/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+([a-zA-Z0-9_-]+)\.([a-zA-Z0-9_-]+):\s+([^\n]+)/", $content, $matches, PREG_SET_ORDER);
            
            if (empty($matches)) {
                $lines = explode("\n", $content);
                $lines = array_filter($lines);
                $lines = array_reverse($lines);
                $lines = array_slice($lines, 0, 500);
                foreach ($lines as $line) {
                    $logs[] = [
                        'timestamp' => 'N/A',
                        'env' => 'N/A',
                        'level' => 'LOG',
                        'message' => $line
                    ];
                }
            } else {
                $matches = array_reverse($matches);
                $matches = array_slice($matches, 0, 500);
                foreach ($matches as $match) {
                    $logs[] = [
                        'timestamp' => $match[1],
                        'env' => $match[2],
                        'level' => $match[3],
                        'message' => $match[4]
                    ];
                }
            }
        }

        return view('logs.index', compact('logs'));
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            File::put($logPath, '');
        }
        return redirect()->back()->with('success', 'Log file cleared successfully.');
    }
}
