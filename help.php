<?php
include_once 'includes/header.php';
include_once 'config/db.php';
include_once 'classes/Support.php';

$database = new Database();
$db = $database->getConnection();
$supportObj = new Support($db);

// Ensure table exists
$supportObj->createTable();

$success_msg = "";
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_support'])) {
    if($supportObj->create($_POST['name'], $_POST['email'], $_POST['type'], $_POST['message'])) {
        $success_msg = "تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="text-center mb-5">
                <h1 class="text-gold mb-3">مركز المساعدة</h1>
                <p class="text-gray-text lead">Help Center</p>
            </div>

            <div class="card-luxury p-4 mb-4">
                <div class="accordion accordion-flush bg-transparent" id="helpAccordion">
                    <div class="accordion-item bg-transparent border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-white py-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#h1">
                                <span class="text-gold me-3">01.</span> كيفية حجز موعد
                            </button>
                        </h2>
                        <div id="h1" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body text-gray-text pb-4">
                                لحجز موعد، ابحث عن الحلاق المناسب لك، اختر الخدمات المطلوبة، حدد التاريخ والوقت المتاحين، ثم اضغط على زر "احجز الآن".
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-white py-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#h2">
                                <span class="text-gold me-3">02.</span> كيفية إلغاء الموعد
                            </button>
                        </h2>
                        <div id="h2" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body text-gray-text pb-4">
                                يمكنك إلغاء موعدك من خلال صفحة "حجوزاتي" في حسابك الشخصي، ثم اختيار الحجز المراد إلغاؤه والضغط على خيار الإلغاء.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent border-bottom border-secondary">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-white py-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#h3">
                                <span class="text-gold me-3">03.</span> كيفية إنشاء حساب
                            </button>
                        </h2>
                        <div id="h3" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body text-gray-text pb-4">
                                اضغط على زر "تسجيل الدخول" أو "انضم إلينا"، اختر نوع الحساب (زبون أو حلاق)، واملأ البيانات المطلوبة لإنشاء حسابك.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-white py-4 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#h4">
                                <span class="text-gold me-3">04.</span> كيفية التواصل مع الحلاق
                            </button>
                        </h2>
                        <div id="h4" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body text-gray-text pb-4">
                                يمكنك العثور على معلومات التواصل (رقم الهاتف) في صفحة الملف الشخصي للحلاق بعد تسجيل الدخول.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5" id="contactSection">
                <p class="text-gray-text">هل لديك أسئلة أخرى؟</p>
                <div class="card-luxury p-4 text-start">
                    <h5 class="text-gold mb-4 text-center">تقديم شكوى أو طلب مساعدة</h5>
                    <?php if($success_msg): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>
                    <form action="help.php#contactSection" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-gray-text small">الاسم الكامل</label>
                                <input type="text" name="name" class="form-control" required placeholder="أدخل اسمك">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-gray-text small">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" required placeholder="example@mail.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-gray-text small">نوع الطلب</label>
                                <select name="type" class="form-select">
                                    <option value="complaint">شكوى (Complaint)</option>
                                    <option value="support">دعم فني (Support)</option>
                                    <option value="other">أخرى (Other)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-gray-text small">الرسالة</label>
                                <textarea name="message" class="form-control" rows="4" required placeholder="اكتب تفاصيل طلبك هنا..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="submit_support" class="btn btn-gold w-100 py-3 mt-2">إرسال الطلب</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button::after {
    filter: invert(72%) sepia(87%) saturate(301%) hue-rotate(1deg) brightness(91%) contrast(88%);
}
.accordion-button:not(.collapsed) {
    background-color: transparent !important;
    color: var(--gold) !important;
    box-shadow: none !important;
}
</style>

<?php include_once 'includes/footer.php'; ?>
