<!DOCTYPE html>
<html>
<head>
    <title>List Search</title>
    <style>
        .merchant-selection {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #87CEEB;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .merchant-selection h2 {
            color: #1e90ff;
            margin-bottom: 15px;
            font-size: 1.4em;
        }
        .data-source {
            text-align: center;
            margin: 10px 0;
        }
        .data-source label {
            padding: 5px 15px;
            margin: 0 5px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .data-source label:hover {
            background: #e6f3ff;
        }
        .data-source label {
            margin: 0 10px;
            cursor: pointer;
        }
        body {
            background-color: #f0f8ff;
            font-family: Arial, sans-serif;
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .search-box { 
            width: 100%; 
            padding: 12px;
            margin-bottom: 2px;
            border: 2px solid #87CEEB;
            border-radius: 5px;
            font-size: 16px;
            margin: 0 auto;
            display: block;
        }
        .dropdown-results { 
            border: 1px solid #87CEEB;
            position: absolute;
            background: white;
            width: calc(100% - 2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: none;
            z-index: 1000;
            border-radius: 0 0 5px 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        .main-results {
            border: 1px solid #87CEEB;
            background: white;
            width: 100%;
            margin: 10px auto;
            display: none;
            border-radius: 5px;
            column-count: 2;
            column-gap: 20px;
        }
        .list-item[style*="font-weight: bold"] {
            column-span: all;
            background: #87CEEB;
            color: white;
            margin-bottom: 10px;
            text-align: center;
            font-size: 18px;
        }
        .list-item { 
            padding: 12px; 
            cursor: pointer;
            border-bottom: 1px solid #e6f3ff;
            transition: background-color 0.2s;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .list-item:hover {
            background: #e6f3ff;
            color: #1e90ff;
        }
        .search-container {
            position: relative;
        }
        .list-item[data-type="list"] {
            color: #1e90ff;
            font-weight: 500;
        }

        .highlight {
            color:#33caff;
            padding: 0px;
            border-radius: 0px;
        }

        .list-item span.parenthetical {
            font-size: 0.85em;
            color: #666;
        }
        .list-item span.parenthetical::before {
            content: " [";
            padding-right: 4px;
        }
        .list-item span.parenthetical::after {
            content: "]";
            padding-left: 4px;
        }


    </style>
</head>
<body>
    <h1 style="text-align: center; color: #1e90ff; margin-bottom: 20px;">Country Product - Search</h1>
    <div class="merchant-selection">
        <h2>Select Merchant or Partner</h2>
        <div class="data-source">
            <label>
                <input type="radio" name="dataSource" value="data1" checked> Merchant Data
            </label>
            <label>
                <input type="radio" name="dataSource" value="data2"> Partner Data
            </label>
        </div>
    </div>
    <div class="container">
        <div class="search-container">
                <div style="display: flex; gap: 10px; margin: 10px 0; align-items: center;">
                    <select id="listSelect" class="search-box" style="margin: 0;">
                        <option value="">Select a country...</option>
                        <?php foreach ($data['lists'] as $list): ?>
                            <option value="<?php echo htmlspecialchars($list['name']); ?>"><?php echo htmlspecialchars($list['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-weight: bold; color: #1e90ff;">OR</div>
                    <select id="sublistSelect" class="search-box" style="margin: 0;">
                        <option value="">Select a product...</option>
                        <?php 
                        $allSublists = [];
                        foreach ($data['lists'] as $list) {
                            $allSublists = array_merge($allSublists, $list['sublists']);
                        }
                        $uniqueSublists = array_unique($allSublists);
                        sort($uniqueSublists);
                        foreach ($uniqueSublists as $sublist): 
                        ?>
                            <option value="<?php echo htmlspecialchars($sublist); ?>"><?php echo htmlspecialchars($sublist); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="text-align: center; margin: 10px 0;">
                    <button id="resetButton" style="padding: 8px 20px; background-color: #1e90ff; color: white; border: none; border-radius: 5px; cursor: pointer;">Reset</button>
                </div>
                <div style="margin-bottom: 10px; display: none;">
                    <input type="text" id="searchInput" class="search-box" placeholder="Enter country or feature ...">
                </div>
            </div>
                <div id="dropdownResults" class="dropdown-results results"></div>
            </div>
        <div id="mainResults" class="main-results" style="margin-top: 20px;"></div>
    </div>

    <script>
        <?php 
            include 'data.php';
            include 'data2.php';
        ?>
        const data1 = <?php echo json_encode($data); ?>;
        const data2 = <?php echo json_encode($data2); ?>;
        let data = data1;

        // Handle data source switching
        document.querySelectorAll('input[name="dataSource"]').forEach(radio => {
            radio.addEventListener('change', function() {
                data = this.value === 'data1' ? data1 : data2;
                if (!data || !data.lists) {
                    console.error('Data not loaded properly');
                    return;
                }
                listSelect.value = '';
                sublistSelect.value = '';
                mainResults.style.display = 'none';
                listSelect.disabled = false;
                sublistSelect.disabled = false;

                // Clear existing options
                listSelect.innerHTML = '<option value="">Select a country...</option>';
                sublistSelect.innerHTML = '<option value="">Select a feature...</option>';

                // Populate countries dropdown
                if (data && data.lists) {
                    data.lists.forEach(list => {
                        const option = document.createElement('option');
                        option.value = list.name;
                        option.textContent = list.name;
                        listSelect.appendChild(option);
                    });

                    // Populate features dropdown
                    const allFeatures = new Set();
                    data.lists.forEach(list => {
                        if (list && (list.sublists || list.countries)) {
                            const items = list.sublists || list.countries || [];
                            items.forEach(feature => {
                                if (feature) allFeatures.add(feature.trim());
                            });
                        }
                    });

                    Array.from(allFeatures).sort().forEach(feature => {
                        const option = document.createElement('option');
                        option.value = feature;
                        option.textContent = feature;
                        sublistSelect.appendChild(option);
                    });
                }
            });
        });

        const searchInput = document.getElementById('searchInput');
        const listSelect = document.getElementById('listSelect');
        const sublistSelect = document.getElementById('sublistSelect');
        const dropdownResults = document.getElementById('dropdownResults');
        const mainResults = document.getElementById('mainResults');

        function checkCombinedSelection() {
            const selectedCountry = listSelect.value;
            const selectedFeature = sublistSelect.value;

            if (selectedCountry && selectedFeature) {
                const selectedList = data.lists.find(list => list.name === selectedCountry);
                const items = selectedList ? (selectedList.sublists || selectedList.countries || []) : [];
                const hasFeature = items.includes(selectedFeature);

                const results = [
                    `<div class="list-item" style="font-weight: bold">Filtered Search:</div>`,
                    `<div class="list-item">Country: ${selectedCountry}</div>`,
                    `<div class="list-item">Feature: ${selectedFeature}</div>`,
                    `<div class="list-item" style="color: ${hasFeature ? 'green' : 'red'}; font-weight: bold">
                        ${hasFeature ? 'YES' : 'NO'}
                    </div>`
                ];
                mainResults.innerHTML = results.join('');
                mainResults.style.display = 'block';
            }
        }

        // Handle list selection
        listSelect.addEventListener('change', function(e) {
            // Disable/Enable feature dropdown based on country selection
            sublistSelect.disabled = e.target.value !== '';

            const selectedFeature = sublistSelect.value;
            if (selectedFeature) {
                checkCombinedSelection();
                return;
            }

            const selectedList = data && data.lists ? data.lists.find(list => list.name === e.target.value) : null;
            if (selectedList) {
                const items = (selectedList.sublists || selectedList.countries || []).filter(item => item);
                const results = [
                    `<div class="list-item" style="font-weight: bold">${data === data1 ? 'Merchant' : 'Partner'} Features in "${selectedList.name}":</div>`,
                    ...items.map(subitem => 
                        `<div class="list-item" data-type="sublist">${subitem}</div>`
                    )
                ];
                mainResults.innerHTML = results.join('');
                mainResults.style.display = 'block';
            }
        });

        // Handle sublist selection
        sublistSelect.addEventListener('change', function(e) {
            // Disable/Enable country dropdown based on feature selection
            listSelect.disabled = e.target.value !== '';

            const selectedCountry = listSelect.value;
            if (selectedCountry) {
                checkCombinedSelection();
                return;
            }

            const selectedSublist = e.target.value;
            if (selectedSublist) {
                const parentLists = data.lists.filter(list => {
                    const items = list.sublists || list.countries || [];
                    return items.includes(selectedSublist);
                });
                const results = [
                    `<div class="list-item" style="font-weight: bold">${data === data1 ? 'Merchant' : 'Partner'} Feature "${selectedSublist}" available in:</div>`,
                    ...parentLists.map(list => `<div class="list-item" data-type="list">${list.name}</div>`)
                ];
                mainResults.innerHTML = results.join('');
                mainResults.style.display = 'block';
            }
        });

        function updateResults(content, container) {
            if (container) {
                container.innerHTML = content;
                container.style.display = 'block';
            }
            mainResults.innerHTML = content;
            mainResults.style.display = 'block';
        }

        // Populate dropdowns on page load
        function populateDropdowns() {
            // Clear existing options
            listSelect.innerHTML = '<option value="">Select a country...</option>';
            sublistSelect.innerHTML = '<option value="">Select a feature...</option>';

            // Populate countries dropdown
            data.lists.forEach(list => {
                const option = document.createElement('option');
                option.value = list.name;
                option.textContent = list.name;
                listSelect.appendChild(option);
            });

            // Populate features dropdown
            const allFeatures = new Set();
            if (data && data.lists) {
                data.lists.forEach(list => {
                    if (list && (list.sublists || list.countries)) {
                        const items = list.sublists || list.countries || [];
                        items.forEach(feature => {
                            if (feature) allFeatures.add(feature.trim());
                        });
                    }
                });
            }

            Array.from(allFeatures).sort().forEach(feature => {
                const option = document.createElement('option');
                option.value = feature;
                option.textContent = feature;
                sublistSelect.appendChild(option);
            });
        }

        // Reset functionality
        document.getElementById('resetButton').addEventListener('click', function() {
            listSelect.value = '';
            sublistSelect.value = '';
            searchInput.value = '';
            mainResults.style.display = 'none';
            listSelect.disabled = false;
            sublistSelect.disabled = false;
        });

        // Call populate function on load
        populateDropdowns();

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let results = [];

            // Reset dropdown values
            listSelect.value = '';
            sublistSelect.value = '';

            if (searchTerm === '') {
                mainResults.style.display = 'none';
                return;
            }

            // Search in both list names and sublists
            let foundItems = new Set();

            data.lists.forEach(list => {
                // Match list names
                if (list.name.toLowerCase().includes(searchTerm)) {
                    results.push(`<div class="list-item" data-type="list">${list.name}</div>`);
                }

                // Match items (either sublists or countries)
                const items = list.sublists || list.countries || [];
                items.forEach(subitem => {
                    if (subitem.toLowerCase().includes(searchTerm) && !foundItems.has(subitem)) {
                        foundItems.add(subitem);
                        const highlightedText = subitem.replace(new RegExp(searchTerm, 'gi'), match => `<span class="highlight">${match}</span>`);
                        results.push(`<div class="list-item" data-type="sublist">${highlightedText}</div>`);
                    }
                });
            });

            if (results.length > 0) {
                mainResults.innerHTML = results.join('');
                mainResults.style.display = 'block';
            } else {
                mainResults.innerHTML = '<div class="list-item">No results found</div>';
                mainResults.style.display = 'block';
            }
        });

        // Close dropdown only when clicking outside and not on a result item
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                dropdownResults.style.display = 'none';
            }
        });

        // Handle option selection
        document.addEventListener('click', function(e) {
            const item = e.target.closest('.list-item');
            if (!item || item.style.fontWeight === 'bold') return;

            const selectedText = item.textContent.split(' [')[0];
            searchInput.value = selectedText;

            // Update dropdowns based on selection type
            if (item.dataset.type === 'list') {
                listSelect.value = selectedText;
                sublistSelect.value = '';
                sublistSelect.disabled = true;
                listSelect.disabled = false;
            } else if (item.dataset.type === 'sublist') {
                sublistSelect.value = selectedText;
                listSelect.value = '';
                listSelect.disabled = true;
                sublistSelect.disabled = false;
            }

            // Hide dropdown and clear its content
            dropdownResults.style.display = 'none';
            dropdownResults.innerHTML = '';
            dropdownResults.innerHTML = '';
            mainResults.style.display = 'block';

            if (item.dataset.type === 'list') {
                // Show all items for the selected list
                const selectedList = data.lists.find(list => list.name === selectedText);
                if (selectedList) {
                    const items = selectedList.sublists || selectedList.countries || [];
                    results = [
                        `<div class="list-item" style="font-weight: bold">${data === data1 ? 'Merchant' : 'Partner'} Features in "${selectedText}":</div>`,
                        ...items.map(subitem => 
                            `<div class="list-item" data-type="sublist">${subitem}</div>`
                        )
                    ];
                    dropdownResults.style.display = 'none';
                    mainResults.innerHTML = results.join('');
                    mainResults.style.display = 'block';
                }
            } else if (item.dataset.type === 'sublist') {
                // Hide dropdown for sublist selection
                dropdownResults.style.display = 'none';
                // Show all lists containing the selected sublist
                const selectedTextClean = selectedText.trim();
                const parentLists = data.lists.filter(list => 
                    (list.sublists && list.sublists.includes(selectedTextClean)) ||
                    (list.countries && list.countries.includes(selectedTextClean))
                );
                results = [
                    `<div class="list-item" style="font-weight: bold">${data === data1 ? 'Merchant' : 'Partner'} Feature "${selectedTextClean}" available in:</div>`,
                    ...parentLists.map(list => `<div class="list-item" data-type="list">${list.name}</div>`)
                ];
                mainResults.innerHTML = results.join('');
            }
        });


    </script>
</body>
</html>