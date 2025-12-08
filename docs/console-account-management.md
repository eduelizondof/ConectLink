# Gestión de Cuentas y Suscripciones via Consola

Esta guía describe cómo administrar cuentas de usuario y suscripciones mediante comandos artisan.

> **Nota:** El registro público está deshabilitado. Todas las cuentas deben crearse mediante estos comandos.

---

## Comandos Disponibles

| Comando | Descripción |
|---------|-------------|
| `account:create` | Crear nueva cuenta de usuario |
| `subscription:show` | Ver detalles de suscripción |
| `subscription:renew` | Renovar o cambiar suscripción |

---

## 1. Crear Cuenta (`account:create`)

Crea un nuevo usuario con suscripción opcional.

### Sintaxis

```bash
php artisan account:create {email} [opciones]
```

### Opciones

| Opción | Descripción | Valor por defecto |
|--------|-------------|-------------------|
| `--name=` | Nombre del usuario | Parte antes del @ del email |
| `--password=` | Contraseña | Generada automáticamente (12 caracteres) |
| `--plan=` | Slug del plan de suscripción | Sin suscripción |
| `--cycle=` | Ciclo de facturación | `annual` |
| `--duration=` | Duración en ciclos | `1` |

### Ciclos de Facturación

| Ciclo | Descripción |
|-------|-------------|
| `monthly` | Mensual |
| `quarterly` | Trimestral (3 meses) |
| `semiannual` | Semestral (6 meses) |
| `annual` | Anual (12 meses) |

### Planes Disponibles

| Slug | Nombre | Precio Mensual | Precio Anual |
|------|--------|----------------|--------------|
| `basico` | Básico | $1.00 USD | $10.00 USD |
| `profesional` | Profesional | $3.00 USD | $30.00 USD |
| `empresarial` | Empresarial | $10.00 USD | $100.00 USD |

### Ejemplos

**Crear usuario básico (sin suscripción):**
```bash
php artisan account:create nuevo@usuario.com
```

**Crear usuario con nombre y contraseña específicos:**
```bash
php artisan account:create juan@empresa.com --name="Juan Pérez" --password="MiPassword123"
```

**Crear usuario con plan profesional anual:**
```bash
php artisan account:create cliente@empresa.com --name="Cliente Nuevo" --plan=profesional --cycle=annual
```

**Crear usuario con plan empresarial por 2 años:**
```bash
php artisan account:create vip@empresa.com --plan=empresarial --cycle=annual --duration=2
```

**Crear usuario con plan básico mensual:**
```bash
php artisan account:create prueba@test.com --plan=basico --cycle=monthly
```

---

## 2. Ver Suscripción (`subscription:show`)

Muestra los detalles de suscripción de un usuario.

### Sintaxis

```bash
php artisan subscription:show {email} [opciones]
```

### Opciones

| Opción | Descripción |
|--------|-------------|
| `--all` | Mostrar todas las suscripciones (incluidas expiradas) |

### Información Mostrada

- Datos de la suscripción activa
- Estado (active, expired, cancelled, pending, trial)
- Fechas de inicio y vencimiento
- Días restantes
- Límites del plan actual
- Historial de pagos

### Ejemplos

**Ver suscripción activa:**
```bash
php artisan subscription:show cliente@empresa.com
```

**Ver historial completo de suscripciones:**
```bash
php artisan subscription:show cliente@empresa.com --all
```

---

## 3. Renovar Suscripción (`subscription:renew`)

Renueva o actualiza la suscripción de un usuario.

### Sintaxis

```bash
php artisan subscription:renew {email} [opciones]
```

### Opciones

| Opción | Descripción | Valor por defecto |
|--------|-------------|-------------------|
| `--plan=` | Nuevo plan (para upgrade/downgrade) | Plan actual |
| `--cycle=` | Ciclo de facturación | Ciclo actual |
| `--duration=` | Duración en ciclos | `1` |
| `--extend` | Extender desde fecha de vencimiento actual | Desde ahora |

### Ejemplos

**Renovar con el mismo plan y ciclo:**
```bash
php artisan subscription:renew cliente@empresa.com
```

**Renovar extendiendo desde la fecha actual de vencimiento:**
```bash
php artisan subscription:renew cliente@empresa.com --extend
```

**Upgrade a plan empresarial:**
```bash
php artisan subscription:renew cliente@empresa.com --plan=empresarial
```

**Cambiar a ciclo mensual:**
```bash
php artisan subscription:renew cliente@empresa.com --cycle=monthly
```

**Renovar por 2 años:**
```bash
php artisan subscription:renew cliente@empresa.com --cycle=annual --duration=2
```

**Upgrade + extensión por 2 años:**
```bash
php artisan subscription:renew cliente@empresa.com --plan=empresarial --cycle=annual --duration=2 --extend
```

---

## Flujos Comunes

### Alta de nuevo cliente

```bash
# 1. Crear cuenta con plan profesional anual
php artisan account:create cliente@nuevo.com --name="Nuevo Cliente" --plan=profesional --cycle=annual

# 2. Verificar que se creó correctamente
php artisan subscription:show cliente@nuevo.com
```

### Renovación de cliente existente

```bash
# 1. Ver estado actual
php artisan subscription:show cliente@existente.com

# 2. Renovar (extender desde vencimiento)
php artisan subscription:renew cliente@existente.com --extend

# 3. Verificar renovación
php artisan subscription:show cliente@existente.com
```

### Upgrade de plan

```bash
# 1. Ver plan actual
php artisan subscription:show cliente@basico.com

# 2. Upgrade a profesional
php artisan subscription:renew cliente@basico.com --plan=profesional

# 3. Verificar cambio
php artisan subscription:show cliente@basico.com
```

### Crear cuenta demo/prueba

```bash
# Cuenta con plan empresarial por 1 mes para demo
php artisan account:create demo@cliente.com --name="Cliente Demo" --plan=empresarial --cycle=monthly --duration=1
```

---

## Notas Importantes

1. **Contraseñas generadas:** Si no especificas `--password`, se genera una automáticamente y se muestra en pantalla. **Guárdala inmediatamente.**

2. **Emails duplicados:** No se permite crear usuarios con emails que ya existen.

3. **Renovación vs Extensión:**
   - Sin `--extend`: La suscripción anterior se marca como expirada y se crea una nueva desde HOY.
   - Con `--extend`: Se crea una nueva suscripción que inicia CUANDO TERMINA la actual.

4. **Referencia de pago:** Cada suscripción genera una referencia automática (ej: `CLI-A8B2C4D6`) para tracking.

5. **Usuarios de demo:** Los usuarios de Dubacano (`dubacano@example.com`, `carlos.vendedor@example.com`, `roberto.ceo@example.com`) tienen suscripciones empresariales anuales.

---

## Troubleshooting

### "User with email already exists"
El email ya está registrado. Usa `subscription:show` para ver el usuario existente.

### "Plan not found"
El slug del plan es incorrecto. Los slugs válidos son: `basico`, `profesional`, `empresarial`.

### "User has no subscription"
El usuario existe pero no tiene suscripción. Usa `subscription:renew` solo funciona si el usuario ya tiene al menos una suscripción histórica. Para agregar suscripción a usuario sin una, crea un nuevo registro directamente o usa el panel de admin.

