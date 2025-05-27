<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    :root {
        --primary: #784ba0;
        --gradient-start: #ff3cac;
        --gradient-end: #38f9d7;
        --surface-dark: #2b2d42;
        --accent: #ffb347;
        --text-main: #f0f0f0;
        --text-dark: #2b2d42;
    }

    .org-card,
    .org-select-wrapper {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        background: rgba(43, 45, 66, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        color: #fff !important;
        border: none !important;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .select2-container--default .select2-selection--single {
        background: rgba(43, 45, 66, 0.3) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
    }

    .select2-dropdown {
        background: rgba(43, 45, 66, 0.95) !important;
        color: #fff !important;
        border-radius: 0.5rem !important;
        border: none !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary) !important;
        color: #fff !important;
    }

    label,
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #fff !important;
    }

    .select2-container--default .select2-selection--single {
        width: 100%;
        height: 38px !important;
        padding: 0.375rem 0.75rem !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        box-sizing: border-box;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        background: rgba(43, 45, 66, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        color: #fff !important;
        border: none !important;
    }

    .select2-container--default .select2-results__option {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        background: rgba(43, 45, 66, 0.3) !important;
        backdrop-filter: blur(10px) !important;
        color: #fff !important;
        border: none !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        top: 50% !important;
        transform: translateY(-50%);
        right: 6px !important;
    }
</style>

<select class="form-select organization-select" id="organizationSelect" name="organization" required <?php if (isset($org_disabled) && $org_disabled)
    echo 'disabled'; ?>>
    <option value="">Select Organization</option>
    <optgroup label="Academic Organization">
        <option value="ACES" title="Association of Computer Engineering Students" <?php if (isset($selected_organization) && $selected_organization == 'ACES')
            echo 'selected'; ?>>ACES</option>
        <option value="AHMS" title="Association of Hospitality Management Students" <?php if (isset($selected_organization) && $selected_organization == 'AHMS')
            echo 'selected'; ?>>AHMS</option>
        <option value="BYTE" title="Beacon of Youth Technology Enthusiasts" <?php if (isset($selected_organization) && $selected_organization == 'BYTE')
            echo 'selected'; ?>>BYTE</option>
        <option value="CODE" title="Computer Scientists and Developers Society" <?php if (isset($selected_organization) && $selected_organization == 'CODE')
            echo 'selected'; ?>>CO:DE</option>
        <option value="FCWTS" title="Federation of Civic Welfare Training Service" <?php if (isset($selected_organization) && $selected_organization == 'FCWTS')
            echo 'selected'; ?>>FCWTS</option>
        <option value="FEO" title="Future Educators Organization" <?php if (isset($selected_organization) && $selected_organization == 'FEO')
            echo 'selected'; ?>>FEO</option>
        <option value="IIEE-CSC" title="Institute of Integrated Electrical Engineers – Council Student Chapters"
            <?php if (isset($selected_organization) && $selected_organization == 'IIEE-CSC')
                echo 'selected'; ?>>
            IIEE-CSC
        </option>
        <option value="JHSO" title="Junior High Student Organization" <?php if (isset($selected_organization) && $selected_organization == 'JHSO')
            echo 'selected'; ?>>JHSO</option>
        <option value="JMA" title="Junior Marketing Association" <?php if (isset($selected_organization) && $selected_organization == 'JMA')
            echo 'selected'; ?>>JMA</option>
        <option value="SHSO" title="Senior High Student Organization" <?php if (isset($selected_organization) && $selected_organization == 'SHSO')
            echo 'selected'; ?>>SHSO</option>
        <option value="SITS" title="Society of Industrial Technology Students" <?php if (isset($selected_organization) && $selected_organization == 'SITS')
            echo 'selected'; ?>>SITS</option>
        <option value="SPEAR" title="Sports Physical Education and Recreation Club" <?php if (isset($selected_organization) && $selected_organization == 'SPEAR')
            echo 'selected'; ?>>SPEAR</option>
    </optgroup>
    <optgroup label="Non-Academic Organization">
        <option value="CCERT" title="CvSU-CCAT Emergency Response Team" <?php if (isset($selected_organization) && $selected_organization == 'CCERT')
            echo 'selected'; ?>>CCERT</option>
        <option value="NEXUS" title="The CvSU-R Nexus (Official Student Publication)" <?php if (isset($selected_organization) && $selected_organization == 'NEXUS')
            echo 'selected'; ?>>NEXUS</option>
        <option value="ROTARACT" title="Rotaract Club of CvSU-CCAT" <?php if (isset($selected_organization) && $selected_organization == 'ROTARACT')
            echo 'selected'; ?>>ROTARACT</option>
        <option value="SEC" title="Sikat E-Sports Club" <?php if (isset($selected_organization) && $selected_organization == 'SEC')
            echo 'selected'; ?>>SEC</option>
    </optgroup>
    <optgroup label="Performing Arts Groups">
        <option value="ARTRADS" <?php if (isset($selected_organization) && $selected_organization == 'ARTRADS')
            echo 'selected'; ?>>ARTRADS Dance Crew</option>
        <option value="CHORALE" title="CvSU-CCAT Chorale" <?php if (isset($selected_organization) && $selected_organization == 'CHORALE')
            echo 'selected'; ?>>Chorale</option>
        <option value="SONIC-PISTONS" title="CvSU-CCAT Sonic Pistons Live Band" <?php if (isset($selected_organization) && $selected_organization == 'SONIC-PISTONS')
            echo 'selected'; ?>>Live Band
        </option>
    </optgroup>
    <optgroup label="Student Body Organization">
        <option value="CSG" title="Central Student Government of CvSU-CCAT" <?php if (isset($selected_organization) && $selected_organization == 'CSG')
            echo 'selected'; ?>>CSG</option>
    </optgroup>
</select>

<script>
    $(document).ready(function () {
        $('.organization-select').select2({
<div class="org-card org-select-wrapper">
    <select class="form-select organization-select" id="organizationSelect" name="organization" required <?php if (isset($org_disabled) && $org_disabled)
        echo 'disabled'; ?>>
        <option value="">Select Organization</option>
        <optgroup label="Academic Organization">
            <option value="ACES" title="Association of Computer Engineering Students" <?php if (isset($selected_organization) && $selected_organization == 'ACES')
                echo 'selected'; ?>>ACES</option>
            <option value="AHMS" title="Association of Hospitality Management Students" <?php if (isset($selected_organization) && $selected_organization == 'AHMS')
                echo 'selected'; ?>>AHMS</option>
            <option value="BYTE" title="Beacon of Youth Technology Enthusiasts" <?php if (isset($selected_organization) && $selected_organization == 'BYTE')
                echo 'selected'; ?>>BYTE</option>
            <option value="CODE" title="Computer Scientists and Developers Society" <?php if (isset($selected_organization) && $selected_organization == 'CODE')
                echo 'selected'; ?>>CO:DE</option>
            <option value="FCWTS" title="Federation of Civic Welfare Training Service" <?php if (isset($selected_organization) && $selected_organization == 'FCWTS')
                echo 'selected'; ?>>FCWTS</option>
            <option value="FEO" title="Future Educators Organization" <?php if (isset($selected_organization) && $selected_organization == 'FEO')
                echo 'selected'; ?>>FEO</option>
            <option value="IIEE-CSC" title="Institute of Integrated Electrical Engineers – Council Student Chapters"
                <?php if (isset($selected_organization) && $selected_organization == 'IIEE-CSC')
                    echo 'selected'; ?>>
                IIEE-CSC
            </option>
            <option value="JHSO" title="Junior High Student Organization" <?php if (isset($selected_organization) && $selected_organization == 'JHSO')
                echo 'selected'; ?>>JHSO</option>
            <option value="JMA" title="Junior Marketing Association" <?php if (isset($selected_organization) && $selected_organization == 'JMA')
                echo 'selected'; ?>>JMA</option>
            <option value="SHSO" title="Senior High Student Organization" <?php if (isset($selected_organization) && $selected_organization == 'SHSO')
                echo 'selected'; ?>>SHSO</option>
            <option value="SITS" title="Society of Industrial Technology Students" <?php if (isset($selected_organization) && $selected_organization == 'SITS')
                echo 'selected'; ?>>SITS</option>
            <option value="SPEAR" title="Sports Physical Education and Recreation Club" <?php if (isset($selected_organization) && $selected_organization == 'SPEAR')
                echo 'selected'; ?>>SPEAR</option>
        </optgroup>
        <optgroup label="Non-Academic Organization">
            <option value="CCERT" title="CvSU-CCAT Emergency Response Team" <?php if (isset($selected_organization) && $selected_organization == 'CCERT')
                echo 'selected'; ?>>CCERT</option>
            <option value="NEXUS" title="The CvSU-R Nexus (Official Student Publication)" <?php if (isset($selected_organization) && $selected_organization == 'NEXUS')
                echo 'selected'; ?>>NEXUS</option>
            <option value="ROTARACT" title="Rotaract Club of CvSU-CCAT" <?php if (isset($selected_organization) && $selected_organization == 'ROTARACT')
                echo 'selected'; ?>>ROTARACT</option>
            <option value="SEC" title="Sikat E-Sports Club" <?php if (isset($selected_organization) && $selected_organization == 'SEC')
                echo 'selected'; ?>>SEC</option>
        </optgroup>
        <optgroup label="Performing Arts Groups">
            <option value="ARTRADS" <?php if (isset($selected_organization) && $selected_organization == 'ARTRADS')
                echo 'selected'; ?>>ARTRADS Dance Crew</option>
            <option value="CHORALE" title="CvSU-CCAT Chorale" <?php if (isset($selected_organization) && $selected_organization == 'CHORALE')
                echo 'selected'; ?>>Chorale</option>
            <option value="SONIC-PISTONS" title="CvSU-CCAT Sonic Pistons Live Band" <?php if (isset($selected_organization) && $selected_organization == 'SONIC-PISTONS')
                echo 'selected'; ?>>Live Band
            </option>
        </optgroup>
        <optgroup label="Student Body Organization">
            <option value="CSG" title="Central Student Government of CvSU-CCAT" <?php if (isset($selected_organization) && $selected_organization == 'CSG')
                echo 'selected'; ?>>CSG</option>
        </optgroup>
    </select>
</div>

<script>
    $(document).ready(function () {
        $('.organization-select').select2({
            width: 'resolve',
            placeholder: 'Select Organization',
            allowClear: true
        });
    });
</script>