-- Devops migrations were implemented after many migrations were made; create the table and add entries for all known migration scripts
CREATE TABLE IF NOT EXISTS `devops_migration` (
    `version` varchar(8) NOT NULL,
    `script` varchar(128) NOT NULL,
    `date` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`version`)
);

INSERT INTO devops_migration (`version`, `script`) VALUES
    ('01', '01_module_reports_inspections_summary_add_role.sql'),
    ('02', '02_module_messaging_ddl.sql'),
    ('03', '03_message_template.sql'),
    ('04', '04_module_core_create_view_inspection_status.sql'),
    ('05', '05_module_messaging_update_message_queue_type.sql'),
    ('06', '06_module_core_rename_message.sql'),
    ('07', '07_add_inspection_personnel_relation_ddl.sql'),
    ('08', '08_create_inspection_hazard_views.sql'),
    ('09', '09_add_approver_id_col_ddl.sql'),
    ('10', '10_update_hf_in_inspection_hazard_views.sql'),
    ('11', '11_update_room_hazard_view__top_level_hazards.sql'),
    ('12', '12_delete_orphan_hazard_assignments.sql'),
    ('13', '13_rename_core_module.sql'),
    ('14', '14_rename_reports_module.sql'),
    ('15', '15_delete_inactive_templates.sql'),
    ('16', '16_add_pickup_lot_other_waste_type_column_ddl.sql'),
    ('17', '17_init_migration_table.sql');
