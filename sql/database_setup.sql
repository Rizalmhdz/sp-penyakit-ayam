CREATE DATABASE sistem_pakar_penyakit_ayam;

DROP DATABASE sistem_pakar_penyakit_ayam;

USE sistem_pakar_penyakit_ayam;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE symptoms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE diseases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    advice TEXT NOT NULL,
    medicine VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    disease_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (disease_code) REFERENCES diseases(code) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE rule_symptoms (
    rule_id INT,
    symptom_code VARCHAR(10),
    FOREIGN KEY (rule_id) REFERENCES rules(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (symptom_code) REFERENCES symptoms(code) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (rule_id, symptom_code)
);

INSERT INTO users (username, password) VALUES 
('admin', '$2y$10$YM2moLoYheAtjSo.ETD9rO3hDBGXWinVUDeStLVS9q6cSsUOSi1tm'), -- password: admin    ||||| debug -> password_hash($password, PASSWORD_BCRYPT)
('user1', '$2y$10$iSwqS9Add1I27oM./tOpPu0DzHPAvlCGpuLyEsZiJEgDhwmrBUyYe'); -- password: user1    ||||| debug -> password_hash($password, PASSWORD_BCRYPT)

INSERT INTO symptoms (code, name) VALUES
('G001', 'Nafsu makan berkurang'),
('G002', 'Sesak napas / terengah-engah'),
('G003', 'Mendengkur'),
('G004', 'Bersin'),
('G005', 'Batuk'),
('G006', 'Diare'),
('G007', 'Produksi telur menurun'),
('G008', 'Membiru'),
('G009', 'Keluar cairan berbusa dari mata'),
('G010', 'Kepala membengkak'),
('G011', 'Kematian mendadak'),
('G012', 'Penampilan lesu'),
('G013', 'Mencret berwarna kehijauan'),
('G014', 'Berjalan sempoyongan'),
('G015', 'Kepala berputar-putar'),
('G016', 'Tubuh kurus'),
('G017', 'Bulu kusam dan berkerut'),
('G018', 'Kotoran berwarna kehitaman yang mengandung darah'),
('G019', 'Wajah pucat'),
('G020', 'Tinja berwarna keputihan'),
('G021', 'Tidur dengan paruh di lantai'),
('G022', 'Duduk membungkuk'),
('G023', 'Terlihat mengantuk dan bulu-bulu berdiri'),
('G024', 'Pilek'),
('G025', 'Kotoran berwarna putih menempel di anus'),
('G026', 'Bergerombol di tempat yang hangat'),
('G027', 'Banyak minum'),
('G028', 'Suka menggelengkan kepala'),
('G029', 'Bulu kasar'),
('G030', 'Jengger bengkak berwarna merah'),
('G031', 'Kaki meradang/lumpuh'),
('G032', 'Pertumbuhan terhambat'),
('G033', 'Keluar cairan dari mata dan hidung'),
('G034', 'Wajah dan mata bengkak'),
('G035', 'Sayap turun'),
('G036', 'Warna bulu kusam dan pucat'),
('G037', 'Ayam tampak lesu dan tidak bergairah');

INSERT INTO diseases (code, name, advice, medicine) VALUES
('P001', 'Avian Influenza', 'Isolasi ayam yang sakit, berikan antibiotik, dan konsultasikan dengan dokter hewan.', 'Vaksin Avian Influenza, Antibiotik, dan Obat Antiviral'),
('P002', 'Kolera Ayam', 'Bersihkan dan desinfeksi kandang, berikan antibiotik, dan lakukan vaksinasi.', 'Antibiotik (amoksisilin, kolistin, florfenicol), Probiotik, dan Suplemen Elektrolit'),
('P003', 'Penyakit Pullorum', 'Isolasi ayam yang terinfeksi, berikan antibiotik yang sesuai, dan pastikan kebersihan serta sanitasi kandang.', 'Tidak ada obat spesifik, pencegahan dengan vaksinasi sangat penting'),
('P004', 'Penyakit Newcastle', 'Lakukan vaksinasi segera, berikan vitamin tambahan, dan jaga kebersihan kandang.', 'Vaksin Newcastle Disease, Vitamin dan Elektrolit'),
('P005', 'Koksidiosis', 'Berikan obat anti-koksidia, jaga kebersihan kandang, dan pastikan sanitasi yang baik.', 'Obat Anti-koksidia (kokcidiostat)'),
('P006', 'Penyakit Gumboro', 'Lakukan vaksinasi pada ayam yang sehat, berikan elektrolit, dan jaga kebersihan kandang.', 'Vaksin Gumboro, Elektrolit'),
('P007', 'Koriza Infeksius', 'Berikan antibiotik, jaga kebersihan kandang, dan pastikan ventilasi yang baik.', 'Antibiotik, Vitamin, dan Obat Tetes Mata'),
('P008', 'Bronkitis Infeksius', 'Lakukan vaksinasi, berikan antibiotik, dan jaga kebersihan serta ventilasi kandang.', 'Vaksin Bronkitis Infeksius, Antibiotik'),
('P009', 'Penyakit Pernafasan Kronis', 'Berikan antibiotik, vitamin, dan jaga kebersihan serta ventilasi kandang.', 'Antibiotik, Vitamin, dan Obat Pernafasan'),
('P010', 'Kolibasilosis', 'Berikan antibiotik, jaga kebersihan kandang, dan pastikan sanitasi yang baik.', 'Antibiotik, Probiotik');

-- Insert Rules
INSERT INTO rules (disease_code) VALUES
('P001'), ('P002'), ('P003'), ('P004'), ('P005'), ('P006'), ('P007'), ('P008'), ('P009'), ('P010');

-- Insert Rule Symptoms
INSERT INTO rule_symptoms (rule_id, symptom_code) VALUES
(1, 'G001'), (1, 'G002'), (1, 'G003'), (1, 'G004'), (1, 'G005'), (1, 'G006'), (1, 'G007'), (1, 'G008'), (1, 'G009'), (1, 'G010'), (1, 'G011'),
(2, 'G001'), (2, 'G002'), (2, 'G003'), (2, 'G006'), (2, 'G007'), (2, 'G012'), (2, 'G013'), (2, 'G017'), (2, 'G023'), (2, 'G027'), (2, 'G030'), (2, 'G031'), (2, 'G032'),
(3, 'G001'), (3, 'G002'), (3, 'G006'), (3, 'G007'), (3, 'G016'), (3, 'G017'), (3, 'G020'), (3, 'G024'), (3, 'G025'), (3, 'G026'),
(4, 'G001'), (4, 'G002'), (4, 'G003'), (4, 'G004'), (4, 'G005'), (4, 'G007'), (4, 'G012'), (4, 'G013'), (4, 'G014'), (4, 'G015'),
(5, 'G001'), (5, 'G007'), (5, 'G016'), (5, 'G017'), (5, 'G018'), (5, 'G019'), (5, 'G033'),
(6, 'G001'), (6, 'G014'), (6, 'G016'), (6, 'G020'), (6, 'G021'), (6, 'G022'), (6, 'G034'), (6, 'G035'), (6, 'G036'),
(7, 'G001'), (7, 'G003'), (7, 'G023'), (7, 'G032'), (7, 'G034'),
(8, 'G001'), (8, 'G003'), (8, 'G004'), (8, 'G005'), (8, 'G006'), (8, 'G007'), (8, 'G008'), (8, 'G012'), (8, 'G023'), (8, 'G024'),
(9, 'G001'), (9, 'G002'), (9, 'G003'), (9, 'G006'), (9, 'G028'), (9, 'G037'),
(10, 'G001'), (10, 'G002'), (10, 'G003'), (10, 'G005'), (10, 'G006'), (10, 'G029'), (10, 'G037');
