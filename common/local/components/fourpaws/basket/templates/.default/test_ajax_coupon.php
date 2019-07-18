<?
/**
 * Приходят данные:
 * $_GET['id'] - id купона
 * $_GET['event'] - apply/cancel - применить/отменить купон
 * ---------------------
 * Возращаются данные:
 * availablecoupons - массив доступности купонов, где
 *   active - купон применен,
 *   disabled - купон не доступен
 */

/* вероятно active купонов может быть несколько,
 * сейчас для теста приходит только 1
 */

$result = array(
    'success' => true,
    'availablecoupons' => array(
        'id_coupon1' => array(
            'active' => $_GET['id'] == 'id_coupon1' && ($_GET['event'] == 'apply' ? true : false),
            'disabled' => false,
            'title' => 'Скидка 50% на Maelfeal',
            'discount' => '50.00',
        ),
        'id_coupon2' => array(
            'active' => $_GET['id'] == 'id_coupon2' && ($_GET['event'] == 'apply' ? true : false),
            'disabled' => false,
            'title' => 'Скидка 20% на Royal Canine',
            'discount' => '60.00',
        ),
        'id_coupon3' => array(
            'active' => $_GET['id'] == 'id_coupon3' && ($_GET['event'] == 'apply' ? true : false),
            'disabled' => false,
            'title' => 'Скидка 35% на Euikanuba',
            'discount' => '70.00',
        ),
        'id_coupon4' => array(
            'active' => $_GET['id'] == 'id_coupon4' && ($_GET['event'] == 'apply' ? true : false),
            'disabled' => true,
            'title' => 'Скидка 12% на все сумки',
            'discount' => '80.00',
        )
    )
);

echo json_encode($result);
?>