# Auto-keeper for ADB Reverse port 8000 (Classy-One Mobile development)
while ($true) {
    try {
        $devices = & adb devices 2>$null
        if ($devices -match "\tdevice") {
            & adb reverse tcp:8000 tcp:8000 2>$null | Out-Null
        }
    } catch {
        # Silent ignore
    }
    Start-Sleep -Seconds 3
}
