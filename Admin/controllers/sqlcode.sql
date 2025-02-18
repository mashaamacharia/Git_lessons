-- Connect to the chuka database
USE chuka;

-- Create the login table
CREATE TABLE login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Registration VARCHAR(50) NOT NULL,
    Faculty VARCHAR(100) NOT NULL,
    IDNo VARCHAR(20) NOT NULL,
    Name VARCHAR(20) ,
    SubmissionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   
);
-- Add new columns to the login table
ALTER TABLE login
ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
ADD COLUMN phone_number VARCHAR(15) NULL,
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add indexes for better performance
ALTER TABLE login
ADD INDEX idx_registration (Registration),
ADD INDEX idx_faculty (Faculty),
ADD INDEX idx_status (status);

-- Insert sample data into the login table (if needed)
INSERT INTO login (Registration, Faculty, IDNo,Name) 
VALUES ('SampleRegNo', 'SampleFaculty', 'SampleIDNo','Macharia');





CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(40) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data into the admins table
-- Passwords are hashed using bcrypt (placeholders below)
INSERT INTO admins (username, password, phone) VALUES
('admin1', 'Supporting1', '0797105298'),
('admin2', 'Supporting2', '0706134493');



CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    position VARCHAR(50),
    faculty VARCHAR(50) NULL,
    email VARCHAR(50) NOT NULL,
    description TEXT,
    photo_path VARCHAR(255),
    deputy_name VARCHAR(100) NULL,
    deputy_photo_path VARCHAR(255) NULL
    FOREIGN KEY (email) REFERENCES details(email)
);
INSERT INTO candidates (name, position, faculty,email) VALUES
('Victor Macharia', 'president', NULL),
('Adrian Macharia', 'president', NULL),
('Faith Chepkoril', 'president', NULL),
('Eunice Wanja', 'deputy', NULL),
('Pamela Wanja', 'deputy', NULL),
('Yvone Ngima', 'deputy', NULL),
('Grace Anari', 'faculty-rep', 'Law'),
('Natasha Nyawira', 'faculty-rep', 'Law'),
('Linet Nyawira', 'faculty-rep', 'Business'),
('David Maina', 'faculty-rep', 'Engineering'),
('Erastus Mugo', 'residence-rep', NULL),
('Edna Muthoni', 'residence-rep', NULL),
('Maureen Wambui', 'non-resident-rep', NULL),
('Evans Bet', 'non-resident-rep', NULL),
('Precious Njeri', 'environment-rep', NULL),
('Moses Lengaresi', 'environment-rep', NULL),
('Safari Lokolonyei', 'sports-rep', NULL),
('CAtherine Mulwa', 'sports-rep', NULL);



CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    position VARCHAR(255) NOT NULL,
    candidate_id INT NOT NULL,
    vote_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES login(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);


CREATE TABLE details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    Registration VARCHAR(50) NOT NULL,
    IDNo VARCHAR(50) NOT NULL,
    UNIQUE (email, Registration, IDNo)
);

-- Insert sample data
INSERT INTO details (email, Registration, IDNo) VALUES
('candidate1@example.com', 'CAND001', '12345678'),
('candidate2@example.com', 'CAND002', '87654321');

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    announcement TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for tracking login attempts (for rate limiting)
CREATE TABLE login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(40),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for logging admin activities
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Table for "Remember Me" functionality
CREATE TABLE auth_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    selector VARCHAR(255) NOT NULL,
    hashed_validator VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Modify the existing admins table to use proper password hashing
ALTER TABLE admins MODIFY COLUMN password VARCHAR(255) NOT NULL;