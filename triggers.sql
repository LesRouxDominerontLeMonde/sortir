USE `sortir_lesroux`;
CREATE PROCEDURE archive_sortie()
BEGIN
   UPDATE `sortie`
        SET `archivee` = 1
        WHERE `etat_id` IN (SELECT `id` FROM `etat` WHERE `libelle` IN ('Clôturée', 'Passée', 'Annulée'))
        AND `updated_at` <= DATE_SUB(CURDATE(), INTERVAL 1 MONTH);
END;

CREATE TRIGGER trg_update_sortie
AFTER UPDATE ON `sortie`
FOR EACH ROW
BEGIN
    CALL archive_sortie();
END;

CREATE TRIGGER trg_create_sortie
    AFTER INSERT ON `sortie`
    FOR EACH ROW
BEGIN
    CALL archive_sortie();
END;

CREATE TRIGGER trg_delete_sortie
    AFTER DELETE ON `sortie`
    FOR EACH ROW
BEGIN
    CALL archive_sortie();
END;