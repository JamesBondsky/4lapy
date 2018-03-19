$(document).ready(function () {
    const template = doT.template(`
        {{~it.intervals :item:i}}
            <h2>{{=item.ZONE_NAME}}</h2>
            <input type="hidden" name="{{=it.inputName}}[{{=i}}][ZONE_CODE]" value="{{=item.ZONE_CODE}}">
            <div class="delivery-intervals" data-zone="{{=i}}">
                <h3>Интервалы доставки</h3>
                <table class="adm-list-table delivery-intervals__list">
                    <thead>
                        <tr class="adm-list-table-header">
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">Название</div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">С</div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">По</div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner"></div>
                            </td>
                        </tr>
                    </thead>
                    {{~item.INTERVALS :interval:j}}
                    <tr class="adm-list-table-row delivery-intervals__list-item" data-item="{{=j}}">
                        <td class="adm-list-table-cell">
                            Интервал {{=j}}
                        </td>
                        <td class="adm-list-table-cell">
                            <input type="number" min="0" max="23" value="{{=interval.FROM}}" name="{{=it.inputName}}[{{=i}}][INTERVALS][{{=j}}][FROM]" data-type="interval" data-from>
                        </td>
                        <td class="adm-list-table-cell">
                            <input type="number" min="0" max="23" value="{{=interval.TO}}" name="{{=it.inputName}}[{{=i}}][INTERVALS][{{=j}}][TO]" data-type="interval" data-to>
                        </td>
                        <td class="adm-list-table-cell">
                            <button class="adm-btn adm-btn-delete js-delivery-interval-action" data-action="delete">Удалить</button>
                        </td>
                    </tr>
                    {{~}}
                    <tr class="adm-list-table-row">
                        <td class="adm-list-table-cell" colspan="3">
                            <button class="adm-btn adm-btn-add js-delivery-interval-action" data-action="add">Добавить</button>
                        </td>
                    </tr>
                </table>
                <h3>Правила интервалов доставки</h3>
                <table class="adm-list-table delivery-intervals__list">
                    <thead>
                        <tr class="adm-list-table-header">
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">Правило с</div>
                            </td>
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">Правило по</div>
                            </td>
                            <td class="adm-list-table-cell">
                            </td>
                            {{~item.INTERVALS :interval:j}}
                            <td class="adm-list-table-cell">
                                <div class="adm-list-table-cell-inner">Интервал {{=j}}</div>
                            </td>
                            {{~}}
                        </tr>
                    </thead>
                    {{~item.RULES.ADD_DAYS :rule:j}}
                    <tr class="adm-list-table-row delivery-intervals__list-item" data-item="{{=j}}">
                        <td class="adm-list-table-cell">
                            <input type="number" min="0" max="23" value="{{=rule.FROM}}" name="{{=it.inputName}}[{{=i}}][RULES][ADD_DAYS][{{=j}}][FROM]" data-type="rule" data-from>
                        </td>
                        <td class="adm-list-table-cell">
                            <input type="number" min="0" max="23" value="{{=rule.TO}}" name="{{=it.inputName}}[{{=i}}][RULES][ADD_DAYS][{{=j}}][TO]" data-type="rule" data-to>
                        </td>
                        <td class="adm-list-table-cell">
                            <button class="adm-btn adm-btn-delete js-delivery-interval-rule-action" data-action="delete">Удалить</button>
                        </td>
                        {{~item.INTERVALS :interval:k}}
                            <td class="adm-list-table-cell">
                                <input type="number" min="0" value="{{=interval.RULES.ADD_DAYS[j]}}" name="{{=it.inputName}}[{{=i}}][INTERVALS][{{=k}}][RULES][ADD_DAYS][{{=j}}]" data-type="interval-rule" data-id="{{=k}}">
                            </td>
                        {{~}}
                    </tr>
                    {{~}}
                    <tr class="adm-list-table-row">
                        <td class="adm-list-table-cell" colspan="3">
                            <button class="adm-btn adm-btn-add js-delivery-interval-rule-action" data-action="add">Добавить</button>
                        </td>
                    </tr>
                </table>
            </div>
        {{~}}
    `);

    const $root = $(deliveryIntervalsComponentMountPoint);
    let data = deliveryIntervalsComponentData;

    function refresh() {
        $root.html(template({
            intervals: data,
            inputName: deliveryIntervalsInputName
        }));
    }

    refresh();

    $(document).on('click', '.js-delivery-interval-action', function (e) {
        e.preventDefault();
        let $this = $(this);
        let action = $this.attr('data-action');
        if (['add', 'delete'].indexOf(action) === -1) {
            return;
        }

        let zoneIndex = $this.closest('.delivery-intervals').attr('data-zone');
        let intervalId = $this.closest('.delivery-intervals__list-item').attr('data-item');
        switch (action) {
            case 'add':
                let intervalData = {FROM: 0, TO: 0, RULES: {'ADD_DAYS': []}};
                for (let i = 0; i < data[zoneIndex].RULES.ADD_DAYS.length; i++) {
                    intervalData.RULES.ADD_DAYS.push(0);
                }
                data[zoneIndex].INTERVALS.push(intervalData);
                break;
            case 'delete':
                data[zoneIndex].INTERVALS.splice(intervalId, 1);
                break;
        }

        refresh();
    });

    $(document).on('click', '.js-delivery-interval-rule-action', function (e) {
        e.preventDefault();
        let $this = $(this);
        let action = $this.attr('data-action');
        if (['add', 'update', 'delete'].indexOf(action) === -1) {
            return;
        }

        let zoneIndex = $this.closest('.delivery-intervals').attr('data-zone');
        let ruleId = $this.closest('.delivery-intervals__list-item').attr('data-item');
        switch (action) {
            case 'add':
                data[zoneIndex].RULES.ADD_DAYS.push({FROM: 0, TO: 0});
                for (let i = 0; i < data[zoneIndex].INTERVALS.length; i++) {
                    data[zoneIndex].INTERVALS[i].RULES.ADD_DAYS.push(0);
                }
                break;
            case 'delete':
                data[zoneIndex].RULES.ADD_DAYS.splice(ruleId, 1);
                for (let i = 0; i < data[zoneIndex].INTERVALS.length; i++) {
                    data[zoneIndex].INTERVALS[i].RULES.ADD_DAYS.splice(ruleId, 1);
                }
                break;
        }

        refresh();
    });

    $(document).on('change', '.delivery-intervals__list-item input', function () {
        let $this = $(this);
        let zoneIndex = $this.closest('.delivery-intervals').attr('data-zone');
        let id = $this.closest('.delivery-intervals__list-item').attr('data-item');

        switch ($this.attr('data-type')) {
            case 'interval':
                if ($this.is('[data-from]')) {
                    data[zoneIndex].INTERVALS[id].FROM = $this.val();
                } else if ($this.is('[data-to]')) {
                    data[zoneIndex].INTERVALS[id].TO = $this.val();
                }
                break;
            case 'rule':
                if ($this.is('[data-from]')) {
                    data[zoneIndex].RULES.ADD_DAYS[id].FROM = $this.val();
                } else if ($this.is('[data-to]')) {
                    data[zoneIndex].RULES.ADD_DAYS[id].TO = $this.val();
                }
                break;
            case 'interval-rule':
                let intervalId = $this.attr('data-id');
                data[zoneIndex].INTERVALS[intervalId].RULES.ADD_DAYS[id] = $this.val();
                break;
        }
    });
});
