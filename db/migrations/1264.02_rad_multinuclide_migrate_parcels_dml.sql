-- Migrate existing parcels to populate their parcel_authorizations
INSERT INTO `parcel_authorization` (`parcel_id`, `authorization_id`, `percentage`)
SELECT `key_id`, `authorization_id`, 100 FROM `parcel`;
