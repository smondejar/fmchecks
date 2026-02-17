-- Add crop region columns to areas table
ALTER TABLE areas
    ADD COLUMN crop_x DECIMAL(10,8) NULL AFTER cal_distance_m,
    ADD COLUMN crop_y DECIMAL(10,8) NULL AFTER crop_x,
    ADD COLUMN crop_w DECIMAL(10,8) NULL AFTER crop_y,
    ADD COLUMN crop_h DECIMAL(10,8) NULL AFTER crop_w;
