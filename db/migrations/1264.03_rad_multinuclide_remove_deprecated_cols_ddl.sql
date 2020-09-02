-- Remove deprecated columns from parcel
ALTER TABLE `parcel` DROP COLUMN  `authorization_id`;
ALTER TABLE `parcel` DROP COLUMN  `isotope_id`;