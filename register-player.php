<?php
require_once 'includes/functions.php';

$currentPage = 'register';
$pageTitle = 'Register Player - ' . SITE_NAME;

$db = getDB();
$error = '';
$success = '';

// Get active categories
$categories = getActiveCategories();

// Age limits per category (min, max in years)
$ageLimits = [
    'U8'     => ['min' => 6,  'max' => 8],
    'U10'    => ['min' => 8,  'max' => 10],
    'U13'    => ['min' => 11, 'max' => 13],
    'U17'    => ['min' => 14, 'max' => 17],
    'Senior' => ['min' => 18, 'max' => 99],
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // === Collect player info ===
    $category_id = intval($_POST['category_id'] ?? 0);
    $first_name  = sanitize($_POST['first_name'] ?? '');
    $last_name   = sanitize($_POST['last_name'] ?? '');
    $gender      = sanitize($_POST['gender'] ?? '');
    $birth_date  = sanitize($_POST['birth_date'] ?? '');
    $language    = sanitize($_POST['language'] ?? '');
    $nationality = sanitize($_POST['nationality'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $phone       = sanitize($_POST['phone'] ?? '');
    $allergies   = sanitize($_POST['allergies'] ?? '');
    $medical     = sanitize($_POST['medical_comment'] ?? '');
    $school      = sanitize($_POST['school'] ?? '');
    $notify_by   = sanitize($_POST['notify_by'] ?? 'Email');
    $referral    = sanitize($_POST['referral_source'] ?? '');
    $signatory   = sanitize($_POST['signatory_name'] ?? '');
    $agreement   = isset($_POST['agreement']) ? 1 : 0;

    // === Validation ===
    if ($category_id <= 0) {
        $error = 'Please select a team category.';
    } elseif (empty($first_name) || empty($last_name)) {
        $error = 'First name and last name are required.';
    } elseif (!in_array($gender, ['Male', 'Female'])) {
        $error = 'Please select a gender.';
    } elseif (empty($birth_date)) {
        $error = 'Birth date is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $error = 'Please provide a valid email address.';
    } elseif (!preg_match('/^[0-9+\s\-()]{7,20}$/', $phone)) {
        $error = 'Please provide a valid phone number.';
    } elseif (empty($nationality)) {
        $error = 'Nationality is required.';
    } elseif (empty($school)) {
        $error = 'School/Kindergarten is required.';
    } elseif (!$agreement) {
        $error = 'You must accept the membership terms.';
    }

    // === Age auto-check ===
    if (!$error) {
        $birth = new DateTime($birth_date);
        $today = new DateTime();
        $age   = $today->diff($birth)->y;

        // Find category
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $cat = $stmt->fetch();

        if (!$cat) {
            $error = 'Invalid team category.';
        } else {
            $limit = $ageLimits[$cat['name']] ?? null;
            if ($limit && ($age < $limit['min'] || $age > $limit['max'])) {
                $error = "Sorry, your age ($age) does not match the {$cat['name']} category (ages {$limit['min']}-{$limit['max']}).";
            }
        }
    }

    // === Contact persons validation ===
    $contactPersons = [];
    if (!$error && isset($_POST['contact_first_name']) && is_array($_POST['contact_first_name'])) {
        foreach ($_POST['contact_first_name'] as $i => $cfn) {
            $cfn = trim($cfn);
            $cln = trim($_POST['contact_last_name'][$i] ?? '');
            $cem = trim($_POST['contact_email'][$i] ?? '');
            $cph = trim($_POST['contact_phone'][$i] ?? '');
            $cty = trim($_POST['contact_type'][$i] ?? '');

            if ($cfn === '' && $cln === '') continue;

            if (empty($cfn) || empty($cln) || empty($cem) || empty($cph) || empty($cty)) {
                $error = 'Please complete all required fields for each contact person.';
                break;
            }
            if (!filter_var($cem, FILTER_VALIDATE_EMAIL)) {
                $error = 'Contact person email is invalid.';
                break;
            }
            if (!preg_match('/^[0-9+\s\-()]{7,20}$/', $cph)) {
                $error = 'Contact person phone is invalid.';
                break;
            }

            $contactPersons[] = [
                'first_name'   => $cfn,
                'last_name'    => $cln,
                'email'        => $cem,
                'phone'        => $cph,
                'contact_type' => $cty,
            ];
        }

        if (empty($contactPersons) && !$error) {
            $error = 'Please add at least one emergency contact person.';
        }
    } elseif (!$error) {
        $error = 'Please add at least one emergency contact person.';
    }

    // === Save to DB ===
    if (!$error) {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO player_registrations
                (category_id, first_name, last_name, gender, birth_date, language, nationality,
                 email, phone, allergies, medical_comment, school, notify_by, referral_source,
                 signatory_name, agreement_accepted, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $category_id, $first_name, $last_name, $gender, $birth_date,
                $language, $nationality, $email, $phone, $allergies, $medical,
                $school, $notify_by, $referral, $signatory, $agreement
            ]);

            $regId = $db->lastInsertId();

            $stmt = $db->prepare("
                INSERT INTO player_registration_contacts
                (registration_id, first_name, last_name, email, phone, contact_type)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            foreach ($contactPersons as $cp) {
                $stmt->execute([$regId, $cp['first_name'], $cp['last_name'], $cp['email'], $cp['phone'], $cp['contact_type']]);
            }

           $db->commit();

          // Notify admin about new registration
            require_once 'includes/notifications.php';
               notifyAdminNewRegistration([
                 'id' => $regId,
                 'first_name' => $first_name,
                 'last_name' => $last_name,
                 'birth_date' => $birth_date,
                 'nationality' => $nationality,
                 'email' => $email,
                 'phone' => $phone
             ], $cat['name'] ?? 'Team');

               $_SESSION['reg_success'] = ['name' => $first_name . ' ' . $last_name, 'id' => $regId];
              header('Location: register-player-success.php');
             exit;

        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Something went wrong. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Hero / Header -->
<section class="category-header">
    <div class="container">
        <div class="category-header-content">
            <div>
                <h1>📝 Register a Player</h1>
                <p style="opacity:0.8;font-size:1.1rem;">Join the CLEDUN FC family today — Building Champions Since 2026</p>
            </div>
        </div>
    </div>
</section>

<section style="padding:50px 0;background:var(--light-bg);">
    <div class="container">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:30px;align-items:start;">

            <!-- FORM -->
            <form method="POST" id="regForm" style="background:var(--white);padding:35px;border-radius:var(--radius);box-shadow:var(--shadow);">

                <?php if ($error): ?>
                    <div class="alert alert-error" style="background:#fef2f2;color:#991b1b;padding:14px;border-left:4px solid #ef4444;border-radius:8px;margin-bottom:20px;">
                        ⚠️ <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- 1. General Information -->
                <h2 style="color:var(--primary);font-size:1.3rem;margin-bottom:20px;border-bottom:2px solid var(--secondary);padding-bottom:8px;">👤 Player's General Information</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Team Category *</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo $cat['icon'] ?? '⚽'; ?> <?php echo $cat['name']; ?>
                                    (<?php echo $cat['age_group']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" class="form-control" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Birth Date *</label>
                        <input type="date" name="birth_date" id="birth_date" class="form-control" required>
                        <small id="ageInfo" style="color:var(--gray-text);font-size:0.8rem;"></small>
                    </div>
                    <div class="form-group">
                        <label>Nationality *</label>
                        <input type="text" name="nationality" class="form-control" placeholder="e.g., Kenyan" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Language</label>
                        <input type="text" name="language" class="form-control" placeholder="e.g., English, Swahili">
                    </div>
                    <div class="form-group">
                        <label>School / Kindergarten *</label>
                        <input type="text" name="school" class="form-control" required>
                    </div>
                </div>

                <!-- 2. Contact Info -->
                <h2 style="color:var(--primary);font-size:1.3rem;margin:30px 0 20px;border-bottom:2px solid var(--secondary);padding-bottom:8px;">📞 Player's Contact Information</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="parent@email.com">
                    </div>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+254..." required>
                    </div>
                </div>

                <!-- 3. Contact Persons -->
                <h2 style="color:var(--primary);font-size:1.3rem;margin:30px 0 12px;border-bottom:2px solid var(--secondary);padding-bottom:8px;">🚨 Emergency Contact Person(s)</h2>
                <p style="color:var(--gray-text);font-size:0.9rem;margin-bottom:15px;">In case of emergency, make sure your club knows who to contact.</p>

                <div id="contactPersons"></div>

                <button type="button" onclick="addContact()" class="btn btn-secondary" style="margin-top:10px;">
                    <i class="fas fa-plus"></i> Add Contact Person
                </button>

                <!-- 4. Medical Info -->
                <h2 style="color:var(--primary);font-size:1.3rem;margin:30px 0 20px;border-bottom:2px solid var(--secondary);padding-bottom:8px;">🏥 Medical Information</h2>

                <div class="form-group">
                    <label>Allergies & Intolerance</label>
                    <textarea name="allergies" class="form-control" rows="2" placeholder="Any allergies we should know about?"></textarea>
                </div>
                <div class="form-group">
                    <label>Medical Comment</label>
                    <textarea name="medical_comment" class="form-control" rows="2" placeholder="Medical conditions, medication, etc."></textarea>
                </div>

                <!-- 5. Other -->
                <h2 style="color:var(--primary);font-size:1.3rem;margin:30px 0 20px;border-bottom:2px solid var(--secondary);padding-bottom:8px;">📌 Other Information</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label>Notify me about RSVP and event updates by</label>
                        <select name="notify_by" class="form-control">
                            <option value="Email">Email</option>
                            <option value="SMS">SMS</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Phone">Phone</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>How did you find us?</label>
                        <select name="referral_source" class="form-control">
                            <option value="">Select</option>
                            <option value="Google Search">Google Search</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="WhatsApp">WhatsApp</option>
                            <option value="Friend/Family">Friend / Family</option>
                            <option value="School">School</option>
                            <option value="Event">Event</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- 6. Terms -->
                <h2 style="color:var(--primary);font-size:1.3rem;margin:30px 0 20px;border-bottom:2px solid var(--secondary);padding-bottom:8px;">📜 Membership Terms and Conditions</h2>

                <div class="form-group">
                    <label>Signatory Name *</label>
                    <input type="text" name="signatory_name" class="form-control" placeholder="Parent / Guardian full name" required>
                </div>

                <div style="background:#fef3c7;padding:15px;border-radius:8px;margin-bottom:20px;border-left:4px solid #f59e0b;">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-weight:600;">
                        <input type="checkbox" name="agreement" value="1" required style="margin-top:4px;">
                        <span>I confirm that I am the parent / legal guardian and I accept the CLEDUN FC membership terms and conditions.</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:1rem;">
                    ✅ Submit Registration
                </button>

            </form>

            <!-- SIDEBAR -->
            <aside style="background:var(--white);padding:25px;border-radius:var(--radius);box-shadow:var(--shadow);position:sticky;top:100px;">
                <h3 style="color:var(--primary);margin-bottom:15px;">📋 Application Checklist</h3>
                <ul style="list-style:none;padding:0;font-size:0.9rem;line-height:2;">
                    <li><i class="fas fa-check" style="color:#10b981;"></i> General information</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Contact information</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Emergency contacts</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Medical information</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Personal information</li>
                    <li><i class="fas fa-check" style="color:#10b981;"></i> Terms & Conditions</li>
                </ul>

                <div style="background:#dbeafe;padding:15px;border-radius:8px;margin-top:20px;font-size:0.85rem;">
                    <strong>💡 Tip:</strong> All fields marked with an asterisk (*) are required. Your application will only be submitted after acceptance of terms.
                </div>

                <div style="margin-top:20px;font-size:0.85rem;color:var(--gray-text);">
                    <strong>📞 Need help?</strong><br>
                    Email: cledunsports@gmail.com<br>
                    WhatsApp: +254 710 339 213
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
// Age limits for auto-check
const ageLimits = {
    'U8':    { min: 6,  max: 8 },
    'U10':   { min: 8,  max: 10 },
    'U13':   { min: 11, max: 13 },
    'U17':   { min: 14, max: 17 },
    'Senior':{ min: 18, max: 99 }
};

const categoryNames = {
    <?php foreach ($categories as $cat): ?>
    '<?php echo $cat['id']; ?>': '<?php echo $cat['name']; ?>',
    <?php endforeach; ?>
};

const birthInput = document.getElementById('birth_date');
const catSelect  = document.getElementById('category_id');
const ageInfo    = document.getElementById('ageInfo');

function checkAge() {
    const birth = birthInput.value;
    const catId = catSelect.value;
    if (!birth || !catId) { ageInfo.textContent = ''; return; }

    const b = new Date(birth);
    const t = new Date();
    let age = t.getFullYear() - b.getFullYear();
    const m = t.getMonth() - b.getMonth();
    if (m < 0 || (m === 0 && t.getDate() < b.getDate())) age--;

    const catName = categoryNames[catId];
    const limit = ageLimits[catName];

    if (limit && (age < limit.min || age > limit.max)) {
        ageInfo.style.color = '#ef4444';
        ageInfo.textContent = `⚠️ Age ${age} does not match ${catName} (${limit.min}-${limit.max} years).`;
    } else {
        ageInfo.style.color = '#10b981';
        ageInfo.textContent = `✅ Age ${age} matches ${catName}.`;
    }
}

birthInput.addEventListener('change', checkAge);
catSelect.addEventListener('change', checkAge);

// Emergency contacts
let contactIndex = 0;
function addContact() {
    const html = `
        <div class="contact-box" style="background:var(--light-bg);padding:18px;border-radius:10px;margin-bottom:15px;position:relative;">
            <button type="button" onclick="this.parentElement.remove()" style="position:absolute;top:10px;right:10px;background:#ef4444;color:white;border:none;width:28px;height:28px;border-radius:50%;cursor:pointer;font-weight:700;">×</button>
            <h4 style="margin-bottom:12px;color:var(--primary);">Contact Person #${contactIndex + 1}</h4>
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="contact_first_name[]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="contact_last_name[]" class="form-control" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="contact_email[]" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Phone *</label>
                    <input type="tel" name="contact_phone[]" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Contact Type *</label>
                <select name="contact_type[]" class="form-control" required>
                    <option value="">Select</option>
                    <option value="Parent">Parent</option>
                    <option value="Guardian">Guardian</option>
                    <option value="Sibling">Sibling</option>
                    <option value="Relative">Relative</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
    `;
    document.getElementById('contactPersons').insertAdjacentHTML('beforeend', html);
    contactIndex++;
}

// Add first contact by default
document.addEventListener('DOMContentLoaded', addContact);
</script>

<?php require_once 'includes/footer.php'; ?>