@echo off
chcp 65001 >nul
cd /d C:\xampp\htdocs\seahum
if not exist "C:\xampp\htdocs\seahum\storage\logs" mkdir "C:\xampp\htdocs\seahum\storage\logs"
set LOG=C:\xampp\htdocs\seahum\storage\logs\eo-setup.txt
echo ===== %date% %time% ===== > "%LOG%"

echo [config:clear] >> "%LOG%"
C:\xampp\php\php.exe artisan config:clear >> "%LOG%" 2>&1

echo [db check] >> "%LOG%"
C:\xampp\php\php.exe artisan tinker --execute="echo 'DB_OK orders=' . DB::table('orders')->count();" >> "%LOG%" 2>&1

echo [route:list] >> "%LOG%"
C:\xampp\php\php.exe artisan route:list --path=webhooks >> "%LOG%" 2>&1

echo [tunnel tool] >> "%LOG%"
where cloudflared >> "%LOG%" 2>&1
where ngrok >> "%LOG%" 2>&1

start "SEAHUM server" cmd /k C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000

where cloudflared >nul 2>&1 && (
  start "SEAHUM tunnel" cmd /k "cloudflared tunnel --url http://127.0.0.1:8000 2>&1 | powershell -NoProfile -Command \"$input | Tee-Object -FilePath C:\xampp\htdocs\seahum\storage\logs\eo-tunnel.txt\""
  goto done
)
where ngrok >nul 2>&1 && (
  start "SEAHUM tunnel" cmd /k ngrok http 8000 --log=stdout --log-format=logfmt ^> C:\xampp\htdocs\seahum\storage\logs\eo-tunnel.txt
  goto done
)
echo NO_TUNNEL_TOOL >> "%LOG%"
:done
echo [done] >> "%LOG%"
