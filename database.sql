-- Incident Management System Database Schema
-- Drop database if exists
DROP DATABASE IF EXISTS incident_management_system;

-- Create database
CREATE DATABASE IF NOT EXISTS incident_management_system;
USE incident_management_system;

-- Create systems table
CREATE TABLE systems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('Server', 'Application', 'Database', 'Firewall', 'Mail Gateway') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create incidents table
CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_type VARCHAR(255) NOT NULL,
    date_time DATETIME NOT NULL,
    affected_system_id INT NOT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    status ENUM('Detected', 'Investigating', 'Resolved') DEFAULT 'Detected',
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (affected_system_id) REFERENCES systems(id) ON DELETE CASCADE
);

-- Create audit_log table
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    record_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    user_identifier VARCHAR(255) NOT NULL,
    old_values TEXT,
    new_values TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_incidents_status ON incidents(status);
CREATE INDEX idx_incidents_severity ON incidents(severity);
CREATE INDEX idx_incidents_date_time ON incidents(date_time);
CREATE INDEX idx_incidents_affected_system ON incidents(affected_system_id);
CREATE INDEX idx_audit_log_table_record ON audit_log(table_name, record_id);
CREATE INDEX idx_audit_log_timestamp ON audit_log(timestamp);

-- Insert sample systems
INSERT INTO systems (name, type, description) VALUES
('Web Server 01', 'Server', 'Main production web server'),
('Customer Portal', 'Application', 'Customer-facing web application'),
('User Database', 'Database', 'Primary user authentication database'),
('Corporate Firewall', 'Firewall', 'Main network firewall'),
('Email Gateway', 'Mail Gateway', 'Corporate email gateway server'),
('API Server 02', 'Server', 'REST API backend server'),
('Admin Dashboard', 'Application', 'Internal admin management system'),
('Transaction DB', 'Database', 'Financial transactions database'),
('Exchange Server', 'Mail Gateway', 'Microsoft Exchange server');

-- Insert sample incidents
INSERT INTO incidents (incident_type, date_time, affected_system_id, severity, status, resolution_notes) VALUES
('Malware Detection', '2026-01-15 10:30:00', 1, 'High', 'Resolved', 'Malware removed successfully. System scanned and cleaned.'),
('Unauthorized Access Attempt', '2026-01-18 14:20:00', 2, 'Critical', 'Investigating', 'Multiple failed login attempts detected. Investigation ongoing.'),
('Data Breach', '2026-01-20 09:15:00', 3, 'Critical', 'Detected', 'Suspicious database queries detected. Initial assessment in progress.'),
('Phishing Email', '2026-01-21 11:45:00', 5, 'Medium', 'Resolved', 'Phishing email blocked and reported. User awareness training conducted.'),
('DDoS Attack', '2026-01-22 16:00:00', 6, 'High', 'Investigating', 'DDoS mitigation in progress. Traffic filtering active.'),
('SQL Injection Attempt', '2026-01-23 08:30:00', 7, 'High', 'Resolved', 'Injection attempt blocked. Input validation strengthened.'),
('Brute Force Attack', '2026-01-23 12:00:00', 8, 'Medium', 'Resolved', 'Attack mitigated. IP addresses blocked.'),
('Email Spoofing', '2026-01-23 13:30:00', 9, 'Low', 'Detected', 'Email spoofing detected. SPF records verified.'),
('Firewall Breach', '2026-01-24 09:00:00', 4, 'Critical', 'Investigating', 'Unauthorized traffic detected through firewall. Rule review in progress.');
