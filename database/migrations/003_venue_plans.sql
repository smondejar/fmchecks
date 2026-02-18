-- Migration 003: Venue Plan Library
-- Stores PDF floor plans at the venue level so they can be reused when creating areas

CREATE TABLE IF NOT EXISTS venue_plans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    venue_id    INT          NOT NULL,
    name        VARCHAR(255) NOT NULL,
    pdf_path    VARCHAR(500) NOT NULL,
    uploaded_by INT          NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id)    REFERENCES venues(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
