<?php
/**
 * @var string $username
 * @var array $orders
 */
?>

<link rel="stylesheet" href="/css/orders/index.css">
<script type="module" src="/js/orders/index.js"></script>

<div class="container header-container">
	<div class="header-container__username">
		<p><?= $username ?></p>
	</div>
	<button id="logout-button" class="button header-container__button">
		Выйти
	</button>
</div>

<div class="container table-container">
	<table class="ui-table">
		<thead>
		<tr>
			<th>
				<input class="ui-table__head-checkbox" type="checkbox">
			</th>
			<th>№</th>
			<th>Время</th>
			<th>Контрагент</th>
			<th>Организация</th>
			<th>Сумма</th>
			<th>Валюта</th>
			<th>Статус</th>
			<th>Когда изменен</th>
		</tr>
		</thead>
		<tbody>
	<?php
	foreach ($orders['items'] as $order):
		?>
			<tr>
				<td>
					<input class="ui-table__row-checkbox" type="checkbox">
				</td>
				<td>
					<a href="<?= $order['number']['link'] ?>">
			  <?= $order['number']['value'] ?>
					</a>
				</td>
				<td><?= $order['created_at'] ?></td>
				<td>
					<a href="<?= $order['agent']['link'] ?>">
			  <?= $order['agent']['value'] ?>
					</a>
				</td>
				<td><?= $order['organization_name'] ?></td>
				<td><?= $order['sum'] ?></td>
				<td><?= $order['currency_name'] ?></td>
				<td>
					<button
						class="ui-table__state-button"
						data-order-uuid="<?= $order['id'] ?>"
						data-state-uuid="<?= $order['state']['id'] ?>"
						style="background-color: <?= '#' . $order['state']['color'] ?>;"
					>
			  <?= $order['state']['name'] ?>
					</button>
				</td>
				<td class="ui-table__updated-at"><?= $order['updated_at'] ?></td>
			</tr>
	<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="5">
				Всего заказов <?= $orders['items_count'] ?>, на сумму <?= $orders['sum_total'] ?> руб.
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		</tfoot>
	</table>

	<div id="dropdown-content" class="dropdown-content" hidden="hidden">
	  <?php
	  foreach ($orders['states'] as $stateId => $stateValues) {
		  echo '<div class="dropdown-content__state-line" data-state-uuid="' . $stateId . '">' .
			  '<span class="dropdown-content__state-line__color-marker" style="background-color: #' . $stateValues['color'] . ';"></span>' .
			  '<span class="dropdown-content__state-line__name">' . htmlspecialchars($stateValues['name']) . '</span>' .
			  '</div>';
	  }
	  ?>
	</div>
</div>