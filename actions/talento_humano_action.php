<?php
// pages/talento-humano.php

define('ROLES_ADMIN', [1, 2, 3]);
$es_admin = isset($_SESSION['user_rol_id']) && in_array($_SESSION['user_rol_id'], ROLES_ADMIN);
?>
<div id="talento-humano-page" class="page-content active">
    <main class="registro-container">
        <section>
            <h1>Gestión de Talento Humano</h1>
            <p>Seleccione las opciones para generar la carta laboral.</p>
        </section>

        <section class="form-section">
            <h2>Generar Carta Laboral</h2>
            <form id="form-generar-documento-th" action="actions/generar_documento_action.php" method="POST" target="_blank">
                <input type="hidden" name="tipo_documento" value="carta_laboral">

                <?php if ($es_admin): ?>
                    <label for="cedula-empleado">Cédula del Empleado:</label>
                    <input type="text" id="cedula-empleado" name="cedula_empleado" data-autocomplete-nombre="nombre-empleado-th" placeholder="Buscar por cédula..." required>
                    <label for="nombre-empleado-th">Nombre del Empleado:</label>
                    <input type="text" id="nombre-empleado-th" name="nombre_empleado" readonly>
                <?php else: ?>
                    <label for="cedula-empleado">Cédula del Empleado:</label>
                    <input type="text" id="cedula-empleado" name="cedula_empleado" value="<?= htmlspecialchars($_SESSION['user_doc'] ?? '') ?>" readonly>
                    <label for="nombre-empleado-th">Nombre del Empleado:</label>
                    <input type="text" id="nombre-empleado-th" name="nombre_empleado" value="<?= htmlspecialchars(($_SESSION['user_nombre'] ?? '') . ' ' . ($_SESSION['user_apellido'] ?? '')) ?>" readonly>
                <?php endif; ?>

                <label for="tipo_salario">Opción de Salario a Incluir:</label>
                <select id="tipo_salario" name="tipo_salario" required>
                    <option value="con_salario_basico">Mostrar salario básico</option>
                    <option value="con_salario_extras">Mostrar salario básico más extras</option>
                    <option value="sin_salario">No mostrar salario</option>
                </select>

                <label for="dirigido_a">Dirigida a:</label>
                <input type="text" id="dirigido_a" name="dirigido_a" value="A quien interese">

                <button type="submit" style="margin-top: 15px;">Generar Carta</button>
            </form>
        </section>

        <hr style="margin: 40px 0;">
        <section class="form-section">
            <h2>Consultar Documentos Solicitados</h2>
            </section>
    </main>
</div>