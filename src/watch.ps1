$watcher = New-Object System.IO.FileSystemWatcher
$watcher.Path = Get-Location
$watcher.IncludeSubdirectories = $true
$watcher.EnableRaisingEvents = $true

$lastExecutionTime = Get-Date

function Show-Log {
  param([string]$message, [string]$color)
  Write-Host ("[$(Get-Date -Format 'HH:mm:ss')] $message") -ForegroundColor $color
}

$scriptBlock = {
  $now = Get-Date
  if (($now - $global:lastExecutionTime).TotalSeconds -lt 1) {
      return
  }
  $global:lastExecutionTime = $now

  Clear-Host
  $start = Get-Date
  $output = & php index.php 2>&1
  $end = Get-Date
  $timer = $end - $start
  $changedPath = $Event.SourceEventArgs.FullPath

  Show-Log "File changed. Running php index.php" "Cyan"
  Show-Log "File changed: $changedPath" "Cyan"
  Show-Log "Execution completed on $($timer.TotalSeconds) seconds." "Green"

  $output | ForEach-Object { Write-Host $_ }
}

Register-ObjectEvent $watcher Changed -Action $scriptBlock | Out-Null
Register-ObjectEvent $watcher Created -Action $scriptBlock | Out-Null
Register-ObjectEvent $watcher Deleted -Action $scriptBlock | Out-Null
Register-ObjectEvent $watcher Renamed -Action $scriptBlock | Out-Null

Write-Host "Monitoring file changes. Press Ctrl+C to exit..." -ForegroundColor Green

while ($true) { Start-Sleep 1 }