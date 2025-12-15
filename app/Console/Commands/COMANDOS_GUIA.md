# Guía Rápida de Comandos de Consola

Esta guía documenta los comandos de Artisan disponibles para gestionar cuentas de usuario y suscripciones en ConectLink.

---

## 📋 Índice

- [Crear Cuenta](#crear-cuenta)
- [Mostrar Suscripción](#mostrar-suscripción)
- [Renovar Suscripción](#renovar-suscripción)
- [Casos de Uso Comunes](#casos-de-uso-comunes)

---

## 🆕 Crear Cuenta

**Comando:** `account:create`

Crea una nueva cuenta de usuario con opción de asignar una suscripción automáticamente.

### Sintaxis

```bash
php artisan account:create {email} [--name=] [--password=] [--plan=] [--cycle=] [--duration=]
```

### Argumentos

- `email` (requerido): Dirección de correo electrónico del usuario

### Opciones

- `--name`: Nombre del usuario (por defecto: parte antes del @ del email)
- `--password`: Contraseña (se genera automáticamente si no se proporciona)
- `--plan`: Slug del plan de suscripción (`basico`, `profesional`, `empresarial`)
- `--cycle`: Ciclo de facturación (`monthly`, `quarterly`, `semiannual`, `annual`) - Por defecto: `annual`
- `--duration`: Duración en ciclos (ej: 1 año si el ciclo es anual) - Por defecto: `1`

### Ejemplos

```bash
# Crear cuenta básica sin suscripción
php artisan account:create usuario@example.com --name="Juan Pérez"

# Crear cuenta con suscripción Empresarial anual
php artisan account:create admin@empresa.com --name="Admin" --plan=empresarial --cycle=annual

# Crear cuenta con suscripción mensual por 3 meses
php artisan account:create test@example.com --plan=profesional --cycle=monthly --duration=3

# Crear cuenta con contraseña personalizada
php artisan account:create usuario@example.com --password="MiPassword123"
```

### Salida

El comando muestra:
- ID del usuario creado
- Nombre
- Email
- Contraseña generada (si no se proporcionó)
- Detalles de la suscripción (si se creó)

---

## 👁️ Mostrar Suscripción

**Comando:** `subscription:show`

Muestra los detalles de las suscripciones de un usuario, incluyendo resumen y límites del plan.

### Sintaxis

```bash
php artisan subscription:show {email} [--all]
```

### Argumentos

- `email` (requerido): Dirección de correo electrónico del usuario

### Opciones

- `--all`: Muestra todas las suscripciones (activas e inactivas), no solo las activas

### Ejemplos

```bash
# Mostrar suscripción activa
php artisan subscription:show usuario@example.com

# Mostrar todas las suscripciones (historial completo)
php artisan subscription:show usuario@example.com --all
```

### Salida

El comando muestra:
- **Detalles de la suscripción:**
  - Plan
  - Estado (con icono de estado)
  - Ciclo de facturación
  - Monto pagado
  - Método de pago
  - Referencia de pago
  - Fechas de inicio y expiración
  - Días restantes
  - Estado activo/inactivo
  - Notas

- **Resumen:**
  - Total de suscripciones
  - Suscripciones activas
  - Si tiene suscripción activa
  - Total pagado

- **Límites del plan** (si tiene suscripción activa):
  - Máximo de organizaciones
  - Máximo de perfiles por organización
  - Máximo de productos por organización
  - Máximo de enlaces personalizados por perfil
  - Máximo de enlaces sociales por perfil
  - Máximo de alertas por perfil
  - Dominio personalizado (habilitado/deshabilitado)
  - Remover branding (habilitado/deshabilitado)
  - Analytics (habilitado/deshabilitado)

---

## 🔄 Renovar Suscripción

**Comando:** `subscription:renew`

Renueva, extiende o actualiza la suscripción de un usuario. También puede crear una nueva suscripción si el usuario no tiene una.

### Sintaxis

```bash
php artisan subscription:renew {email} [--plan=] [--cycle=] [--duration=] [--extend]
```

### Argumentos

- `email` (requerido): Dirección de correo electrónico del usuario

### Opciones

- `--plan`: Slug del plan (`basico`, `profesional`, `empresarial`). Si no se especifica, mantiene el plan actual
- `--cycle`: Ciclo de facturación (`monthly`, `quarterly`, `semiannual`, `annual`). Si no se especifica, usa el ciclo actual o `annual` por defecto
- `--duration`: Duración en ciclos - Por defecto: `1`
- `--extend`: Extiende desde la fecha de expiración actual en lugar de desde ahora

### Ejemplos

#### Crear nueva suscripción (usuario sin suscripción)

```bash
# Crear suscripción Empresarial anual para usuario sin suscripción
php artisan subscription:renew usuario@example.com --plan=empresarial --cycle=annual
```

#### Renovar suscripción existente

```bash
# Renovar con el mismo plan y ciclo (desde ahora)
php artisan subscription:renew usuario@example.com

# Renovar con el mismo plan pero cambiar a ciclo anual
php artisan subscription:renew usuario@example.com --cycle=annual

# Renovar por 2 años
php artisan subscription:renew usuario@example.com --cycle=annual --duration=2
```

#### Actualizar plan

```bash
# Cambiar de plan Básico a Empresarial
php artisan subscription:renew usuario@example.com --plan=empresarial

# Cambiar plan y ciclo
php artisan subscription:renew usuario@example.com --plan=profesional --cycle=monthly
```

#### Extender suscripción

```bash
# Extender desde la fecha de expiración actual (no desde ahora)
php artisan subscription:renew usuario@example.com --extend

# Extender por 1 año adicional desde la fecha de expiración
php artisan subscription:renew usuario@example.com --extend --cycle=annual --duration=1
```

### Comportamiento

- **Si el usuario NO tiene suscripción:**
  - Requiere `--plan` obligatoriamente
  - Crea una nueva suscripción activa desde ahora

- **Si el usuario SÍ tiene suscripción:**
  - Muestra la suscripción actual antes de renovar
  - Si NO usa `--extend`: Marca la suscripción anterior como `expired` y crea una nueva desde ahora
  - Si usa `--extend`: Crea una nueva suscripción que comienza desde la fecha de expiración de la anterior

### Salida

El comando muestra:
- Suscripción actual (si existe)
- Detalles de la nueva suscripción creada:
  - ID
  - Plan
  - Ciclo
  - Estado
  - Monto
  - Fechas de inicio y expiración
  - Días restantes

---

## 💡 Casos de Uso Comunes

### 1. Crear cuenta completa con suscripción

```bash
php artisan account:create cliente@empresa.com \
  --name="Cliente Empresa" \
  --plan=empresarial \
  --cycle=annual
```

### 2. Agregar suscripción a usuario existente

```bash
php artisan subscription:renew usuario@example.com --plan=empresarial --cycle=annual
```

### 3. Verificar estado de suscripción

```bash
php artisan subscription:show usuario@example.com
```

### 4. Renovar suscripción que está por vencer

```bash
# Renovar desde ahora (reemplaza la actual)
php artisan subscription:renew usuario@example.com

# O extender desde la fecha de expiración (agrega tiempo)
php artisan subscription:renew usuario@example.com --extend
```

### 5. Actualizar plan de suscripción

```bash
# De Básico a Profesional
php artisan subscription:renew usuario@example.com --plan=profesional

# De cualquier plan a Empresarial con ciclo anual
php artisan subscription:renew usuario@example.com --plan=empresarial --cycle=annual
```

### 6. Ver historial completo de suscripciones

```bash
php artisan subscription:show usuario@example.com --all
```

### 7. Crear cuenta de prueba temporal

```bash
php artisan account:create test@example.com \
  --name="Usuario Prueba" \
  --plan=basico \
  --cycle=monthly \
  --duration=1
```

---

## 📝 Notas Importantes

1. **Contraseñas generadas:** Si no se proporciona contraseña, se genera una aleatoria de 12 caracteres que se muestra en la salida del comando.

2. **Planes disponibles:** Los slugs de planes comunes son:
   - `basico` - Plan Básico
   - `profesional` - Plan Profesional
   - `empresarial` - Plan Empresarial

3. **Ciclos de facturación:**
   - `monthly` - Mensual
   - `quarterly` - Trimestral (3 meses)
   - `semiannual` - Semestral (6 meses)
   - `annual` - Anual (12 meses)

4. **Renovación vs Extensión:**
   - **Renovación normal** (`--extend` NO usado): La nueva suscripción comienza desde ahora, la anterior se marca como expirada
   - **Extensión** (`--extend` usado): La nueva suscripción comienza desde la fecha de expiración de la anterior, agregando tiempo adicional

5. **Validaciones:**
   - No se puede crear un usuario con un email que ya existe
   - No se puede renovar sin especificar plan si el usuario no tiene suscripción previa
   - Los planes deben existir en la base de datos

---

## 🔍 Ayuda Adicional

Para ver la ayuda detallada de cualquier comando:

```bash
php artisan account:create --help
php artisan subscription:show --help
php artisan subscription:renew --help
```

---

## 📞 Soporte

Si encuentras algún problema o necesitas ayuda adicional, consulta la documentación completa del proyecto o contacta al equipo de desarrollo.

