-- Add radius and custom color fields to check_points table

ALTER TABLE check_points
ADD COLUMN radius INT UNSIGNED DEFAULT 10 COMMENT 'Circle radius in pixels',
ADD COLUMN custom_colour VARCHAR(7) DEFAULT NULL COMMENT 'Optional custom hex color (overrides type color)';
