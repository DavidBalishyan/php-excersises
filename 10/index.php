<?php
/**
 * Տվյալների շտեմարանի պահուստավորման համակարգ
 * Օգտագործում է mysqldump գործիքը PHP-ի exec ֆունկցիայի միջոցով
 */

class DatabaseBackup {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "testdb";
    private $backupDir = "backups";
    private $fileName;

    public function __construct($host, $username, $password, $database, $backupDir = 'backups') {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->backupDir = $backupDir;
        
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function createBackup() {
        $this->fileName = $this->database . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $this->backupDir . '/' . $this->fileName;
        
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s',
            escapeshellarg($this->host),
            escapeshellarg($this->username),
            escapeshellarg($this->password),
            escapeshellarg($this->database),
            escapeshellarg($filePath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            return [
                'success' => true,
                'message' => 'Backup successfully created',
                'file' => $filePath,
                'size' => $this->getFileSize($filePath)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error creating backup',
                'error_code' => $returnCode
            ];
        }
    }
    
    public function compressBackup() {
        $filePath = $this->backupDir . '/' . $this->fileName;
        $compressedPath = $filePath . '.gz';
        
        $command = sprintf('gzip -9 -c %s > %s', 
            escapeshellarg($filePath),
            escapeshellarg($compressedPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            unlink($filePath);
            
            return [
                'success' => true,
                'message' => 'Backup successfully compressed',
                'file' => $compressedPath,
                'size' => $this->getFileSize($compressedPath)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error compressing backup',
                'error_code' => $returnCode
            ];
        }
    }
    
    public function getBackupFiles() {
        $files = glob($this->backupDir . '/*.{sql,gz}', GLOB_BRACE);
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'file' => basename($file),
                'path' => $file,
                'size' => $this->getFileSize($file),
                'date' => filemtime($file)
            ];
        }
        
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $backups;
    }
    
    public function cleanOldBackups($keepDays = 30) {
        $files = $this->getBackupFiles();
        $cutoffTime = time() - ($keepDays * 86400);
        $removedCount = 0;
        
        foreach ($files as $file) {
            if ($file['date'] < $cutoffTime) {
                unlink($file['path']);
                $removedCount++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Removed $removedCount old backups",
            'removed_count' => $removedCount
        ];
    }
    
    private function getFileSize($file) {
        $bytes = filesize($file);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}

$config = [
    'host' => 'localhost',
    'username' => 'db_user',
    'password' => 'db_password',
    'database' => 'my_database',
    'backup_dir' => __DIR__ . '/backups',
    'keep_days' => 30
];

$backup = new DatabaseBackup(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database'],
    $config['backup_dir']
);

$result = $backup->createBackup();
if ($result['success']) {
    echo "Backup created: " . $result['file'] . " (" . $result['size'] . ")\n";
    
    $compressResult = $backup->compressBackup();
    if ($compressResult['success']) {
        echo "Backup compressed: " . $compressResult['file'] . " (" . $compressResult['size'] . ")\n";
    }
    
    $cleanResult = $backup->cleanOldBackups($config['keep_days']);
    echo $cleanResult['message'] . "\n";
} else {
    echo "Error: " . $result['message'] . "\n";
}