-- =============================================
-- Lab Command Injection - Database Initialization
-- =============================================

CREATE TABLE IF NOT EXISTS dns_lookup_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    host VARCHAR(255) NOT NULL,
    result TEXT,
    user_ip VARCHAR(45),
    queried_at DATETIME DEFAULT NOW()
);

INSERT INTO dns_lookup_log (host, result, user_ip) VALUES
('google.com',     '142.250.185.46',  '192.168.1.1'),
('cloudflare.com', '104.16.123.96',   '192.168.1.2'),
('github.com',     '140.82.114.4',    '192.168.1.3');

CREATE TABLE IF NOT EXISTS ping_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target VARCHAR(255) NOT NULL,
    output TEXT,
    user_ip VARCHAR(45),
    executed_at DATETIME DEFAULT NOW()
);

INSERT INTO ping_log (target, output, user_ip) VALUES
('8.8.8.8',   'PING 8.8.8.8: 3 packets transmitted, 3 received',   '10.0.0.1'),
('1.1.1.1',   'PING 1.1.1.1: 3 packets transmitted, 3 received',   '10.0.0.2');

CREATE TABLE IF NOT EXISTS whois_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL,
    output TEXT,
    queried_at DATETIME DEFAULT NOW()
);
