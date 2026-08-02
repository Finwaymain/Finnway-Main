#!/bin/bash

# Configuration
APP_DIR="/home/shivajee/Desktop/fiinway/Fiinway-main"
DB_CONTAINER="fiinway-mysql"
PID_FILE="$APP_DIR/.artisan.pid"
PORT=8000

cd "$APP_DIR"

start_services() {
    echo "Starting Fiinway Dev Environment..."
    
    # 1. Start MySQL Docker Container
    if [ "$(docker inspect -f '{{.State.Running}}' $DB_CONTAINER 2>/dev/null)" = "true" ]; then
        echo "✔ MySQL container ($DB_CONTAINER) is already running."
    else
        echo "Starting MySQL container ($DB_CONTAINER)..."
        docker start $DB_CONTAINER
        if [ $? -eq 0 ]; then
            echo "✔ MySQL container started successfully."
        else
            echo "❌ Failed to start MySQL container. Please check if docker is running."
            exit 1
        fi
    fi

    # Wait for MySQL to be ready (host port 3308)
    echo "Waiting for database connection on port 3308..."
    for i in {1..15}; do
        if nc -z 127.0.0.1 3308 >/dev/null 2>&1; then
            echo "✔ Database is ready."
            break
        fi
        sleep 1
    done

    # 2. Start Laravel Artisan Server
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if ps -p $PID > /dev/null; then
            echo "✔ Laravel dev server is already running (PID: $PID) on http://127.0.0.1:$PORT"
            return
        fi
    fi

    echo "Starting Laravel dev server on http://127.0.0.1:$PORT..."
    php artisan serve --port=$PORT > /dev/null 2>&1 &
    PID=$!
    echo $PID > "$PID_FILE"
    
    # Wait for server to start
    sleep 2
    if ps -p $PID > /dev/null; then
        echo "✔ Laravel dev server started successfully (PID: $PID)."
        echo "👉 Admin dashboard is accessible at: http://127.0.0.1:$PORT"
    else
        echo "❌ Failed to start Laravel dev server. Check your environment setup."
        rm -f "$PID_FILE"
    fi
}

stop_services() {
    echo "Stopping Fiinway Dev Environment..."
    
    # 1. Stop Laravel Artisan Server
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if ps -p $PID > /dev/null; then
            echo "Stopping Laravel dev server (PID: $PID)..."
            kill $PID
            sleep 1
            if ps -p $PID > /dev/null; then
                kill -9 $PID
            fi
            echo "✔ Laravel dev server stopped."
        else
            echo "Laravel dev server was not running."
        fi
        rm -f "$PID_FILE"
    else
        # Fallback check for any orphaned artisan serve processes
        PID=$(pgrep -f "artisan serve")
        if [ ! -z "$PID" ]; then
            echo "Killing orphaned artisan serve process (PID: $PID)..."
            kill $PID
        else
            echo "Laravel dev server was not running."
        fi
    fi

    # 2. Stop MySQL Docker Container
    if [ "$(docker inspect -f '{{.State.Running}}' $DB_CONTAINER 2>/dev/null)" = "true" ]; then
        echo "Stopping MySQL container ($DB_CONTAINER)..."
        docker stop $DB_CONTAINER
        echo "✔ MySQL container stopped."
    else
        echo "MySQL container is already stopped."
    fi
}

show_status() {
    echo "Fiinway Services Status:"
    
    # Check MySQL
    if [ "$(docker inspect -f '{{.State.Running}}' $DB_CONTAINER 2>/dev/null)" = "true" ]; then
        echo -e "  MySQL Container ($DB_CONTAINER): \033[0;32mRUNNING\033[0m"
    else
        echo -e "  MySQL Container ($DB_CONTAINER): \033[0;31mSTOPPED\033[0m"
    fi

    # Check Laravel Server
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if ps -p $PID > /dev/null; then
            echo -e "  Laravel Dev Server (PID: $PID): \033[0;32mRUNNING\033[0m (http://127.0.0.1:$PORT)"
        else
            echo -e "  Laravel Dev Server (PID: $PID): \033[0;31mSTOPPED\033[0m (Stale PID file)"
        fi
    else
        echo -e "  Laravel Dev Server: \033[0;31mSTOPPED\033[0m"
    fi
}

case "$1" in
    start)
        start_services
        ;;
    stop)
        stop_services
        ;;
    restart)
        stop_services
        start_services
        ;;
    status)
        show_status
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status}"
        exit 1
        ;;
esac
