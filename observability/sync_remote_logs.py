import os
import sys
import paramiko
from datetime import datetime

# Set up local directories
REMOTE_LOGS_DIR = os.path.join(os.path.dirname(__file__), "server_logs")
os.makedirs(REMOTE_LOGS_DIR, exist_ok=True)
LOCAL_LOG_FILE = os.path.join(REMOTE_LOGS_DIR, "laravel.log")
BACKEND_LOG_FILE = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "storage", "logs", "laravel.log"))

def sync_logs():
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    print(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Connecting to server to tail remote logs...")
    try:
        # Connect to the VPS server
        client.connect("187.127.187.72", port=22, username="root", password="Password00911@1")
        
        # Tail the live log
        remote_log_path = "/var/www/fiinway-backend/storage/logs/laravel.log"
        cmd = f"tail -n 1000 -F {remote_log_path}"
        stdin, stdout, stderr = client.exec_command(cmd)
        
        print(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] Started capturing logs -> {LOCAL_LOG_FILE} & {BACKEND_LOG_FILE}. Press Ctrl+C to stop.")
        
        # Ensure backend log directory exists
        os.makedirs(os.path.dirname(BACKEND_LOG_FILE), exist_ok=True)

        # Open local log files in append mode 
        with open(LOCAL_LOG_FILE, 'a') as f1, open(BACKEND_LOG_FILE, 'a') as f2:
            for line in iter(stdout.readline, ""):
                f1.write(line)
                f1.flush()
                f2.write(line)
                f2.flush()
                # Optionally print to console too
                sys.stdout.write(line)
                sys.stdout.flush()

    except KeyboardInterrupt:
        print("\nStopped syncing logs.")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        client.close()

if __name__ == "__main__":
    sync_logs()
