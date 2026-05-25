<?php
include_once '../includes/header.php';
include_once '../config/db.php';
include_once '../classes/Barber.php';

$database = new Database();
$db = $database->getConnection();
$barber = new Barber($db);

$filters = [
    'query' => $_GET['query'] ?? '',
    'city' => $_GET['city'] ?? ($_SESSION['user_city'] ?? ''),
    'type' => $_GET['type'] ?? ''
];

$barbers = $barber->searchBarbers($filters);
$cities = ["Casablanca", "Rabat", "Marrakech", "Fes", "Tangier", "Agadir", "Meknes", "Oujda", "Kenitra", "Tetouan"];
?>

<div class="container py-5">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card-luxury p-4 sticky-top" style="top: 100px;">
                <h4 class="text-gold mb-4">Filters</h4>
                <form action="search.php" method="GET">
                    <div class="mb-3">
                        <label class="form-label text-gray-text">Search</label>
                        <input type="text" name="query" class="form-control" placeholder="Salon or Barber..." value="<?php echo $filters['query']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-gray-text">City</label>
                        <select name="city" class="form-select">
                            <option value="">All Morocco</option>
                            <?php foreach($cities as $city): ?>
                                <option value="<?php echo $city; ?>" <?php if($filters['city'] == $city) echo 'selected'; ?>><?php echo $city; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-gray-text">Salon Type</label>
                        <select name="type" class="form-select">
                            <option value="">Any</option>
                            <option value="men" <?php if($filters['type'] == 'men') echo 'selected'; ?>>Men Only</option>
                            <option value="women" <?php if($filters['type'] == 'women') echo 'selected'; ?>>Women Only</option>
                            <option value="unisex" <?php if($filters['type'] == 'unisex') echo 'selected'; ?>>Unisex</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold w-100 mt-3">Apply Filters</button>
                    <a href="search.php" class="btn btn-outline-light btn-sm w-100 mt-2">Reset</a>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-9">
            <h2 class="mb-4">Top Rated Barbers in <span class="text-gold"><?php echo $filters['city'] ?: 'Morocco'; ?></span></h2>
            
            <div class="row g-4">
                <?php foreach($barbers as $b): ?>
                <div class="col-md-6">
                    <div class="card-luxury p-0 overflow-hidden h-100 d-flex flex-column">
                        <div class="position-relative">
                            <!-- Salon image with 16:9 aspect ratio -->
                            <img src="<?php echo $b['salon_img'] ? '/uploads/salons/'.$b['salon_img'] : 'https://images.unsplash.com/photo-1512690196236-d5a232ebbc74?auto=format&fit=crop&w=600&q=80'; ?>" class="card-img-top" style="aspect-ratio: 16/9; width: 100%; object-fit: cover; height: auto; display: block;">
                            
                            <!-- Rating badge -->
                            <div class="position-absolute top-0 end-0 p-3" style="z-index: 5;">
                                <span class="badge bg-gold text-dark fs-6"><i class="fas fa-star me-1"></i> <?php echo $b['rating']; ?></span>
                            </div>

                            <!-- Overlaid small coiffeur picture with small salon logo inside it -->
                            <div class="position-absolute" style="bottom: -30px; left: 20px; z-index: 4;">
                                <div class="position-relative" style="width: 75px; height: 75px;">
                                    <img src="<?php echo $b['profile_pic'] ? '/uploads/profiles/'.$b['profile_pic'] : 'https://ui-avatars.com/api/?name='.urlencode($b['full_name']); ?>" class="rounded-circle border border-3 border-gold bg-black" style="width: 75px; height: 75px; object-fit: cover;">
                                    <?php if($b['salon_logo']): ?>
                                        <img src="/uploads/salons/<?php echo $b['salon_logo']; ?>" class="position-absolute rounded-circle border border-2 border-gold bg-black" style="width: 28px; height: 28px; bottom: -2px; right: -2px; object-fit: cover; z-index: 5;" title="<?php echo htmlspecialchars($b['salon_name']); ?>">
                                    <?php else: ?>
                                        <img src="https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=100&q=80" class="position-absolute rounded-circle border border-2 border-gold bg-black" style="width: 28px; height: 28px; bottom: -2px; right: -2px; object-fit: cover; z-index: 5;" title="<?php echo htmlspecialchars($b['salon_name']); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 pt-5 flex-grow-1 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h4 class="text-white mb-0"><?php echo $b['full_name']; ?></h4>
                                    <p class="text-gold small mb-0"><?php echo $b['salon_name']; ?></p>
                                </div>
                                <span class="text-gray-text small"><?php echo $b['city']; ?></span>
                            </div>
                            <div class="text-gray-text small mb-4">
                                <span><i class="fas fa-briefcase me-2"></i> <?php echo $b['experience_years']; ?> Years Experience</span>
                                <br>
                                <span><i class="fas fa-venus-mars me-2"></i> <?php echo ucfirst($b['salon_type']); ?> Salon</span>
                            </div>
                            <a href="barber_view.php?id=<?php echo $b['id']; ?>" class="btn btn-outline-gold w-100 mt-auto">View Profile & Book</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if(empty($barbers)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-4x text-gray-text mb-3"></i>
                        <h4>No barbers found matching your criteria.</h4>
                        <p class="text-gray-text">Try adjusting your filters or search for another city.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
