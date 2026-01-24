-- Cybersecurity Incident Management System Database
-- Create database
CREATE DATABASE IF NOT EXISTS cybersecurity_incidents;
USE cybersecurity_incidents;

-- Table for systems/assets
CREATE TABLE IF NOT EXISTS systems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('Server', 'Application', 'Database', 'Mail Gateway') NOT NULL,
    description TEXT,
    status ENUM('Active', 'Inactive', 'Maintenance') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for incidents
CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_type VARCHAR(255) NOT NULL,
    date_time DATETIME NOT NULL,
    affected_system_id INT NOT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    status ENUM('Detected', 'Investigating', 'Resolved') DEFAULT 'Detected',
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (affected_system_id) REFERENCES systems(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample systems
INSERT INTO systems (name, type, description, status) VALUES
('Web Server 01', 'Server', 'Main production web server', 'Active'),
('Customer Portal', 'Application', 'Customer-facing web application', 'Active'),
('User Database', 'Database', 'Primary user authentication database', 'Active'),
('Email Gateway', 'Mail Gateway', 'Corporate email gateway server', 'Active'),
('API Server 02', 'Server', 'REST API backend server', 'Active'),
('Admin Dashboard', 'Application', 'Internal admin management system', 'Active'),
('Transaction DB', 'Database', 'Financial transactions database', 'Active'),
('Exchange Server', 'Mail Gateway', 'Microsoft Exchange server', 'Active');

-- Insert sample incidents
INSERT INTO incidents (incident_type, date_time, affected_system_id, severity, status, resolution_notes) VALUES
('Malware Detection', '2026-01-15 10:30:00', 1, 'High', 'Resolved', 'Malware removed successfully. System scanned and cleaned.'),
('Unauthorized Access Attempt', '2026-01-18 14:20:00', 2, 'Critical', 'Investigating', 'Multiple failed login attempts detected. Investigation ongoing.'),
('Data Breach', '2026-01-20 09:15:00', 3, 'Critical', 'Detected', 'Suspicious database queries detected. Initial assessment in progress.'),
('Phishing Email', '2026-01-21 11:45:00', 4, 'Medium', 'Resolved', 'Phishing email blocked and reported. User awareness training conducted.'),
('DDoS Attack', '2026-01-22 16:00:00', 5, 'High', 'Investigating', 'DDoS mitigation in progress. Traffic filtering active.'),
('SQL Injection Attempt', '2026-01-23 08:30:00', 6, 'High', 'Resolved', 'Injection attempt blocked. Input validation strengthened.'),
('Brute Force Attack', '2026-01-23 12:00:00', 7, 'Medium', 'Resolved', 'Attack mitigated. IP addresses blocked.'),
('Email Spoofing', '2026-01-23 13:30:00', 8, 'Low', 'Detected', 'Email spoofing detected. SPF records verified.');
