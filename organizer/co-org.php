<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    .select2-container--default .select2-selection--single {
        width: 100% !important;
        height: 38px !important;
        padding: 0.375rem 0.75rem !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        box-sizing: border-box;
        background-color: #fff !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-results__option {
        color: #111 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #111 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        top: 50% !important;
        transform: translateY(-50%);
        right: 6px !important;
    }
</style>

</style>

<select class="form-select co-organization-select" name="co_organizer_org[]">
    <option value="">Select Organization</option>
    <optgroup label="Academic Organization">
        <option value="ACES" title="Association of Computer Engineering Students">ACES</option>
        <option value="AHMS" title="Association of Hospitality Management Students">AHMS</option>
        <option value="BYTE" title="Beacon of Youth Technology Enthusiasts">BYTE</option>
        <option value="CODE" title="Computer Scientists and Developers Society">CO:DE</option>
        <option value="FCWTS" title="Federation of Civic Welfare Training Service">FCWTS</option>
        <option value="FEO" title="Future Educators Organization">FEO</option>
        <option value="IIEE-CSC" title="Institute of Integrated Electrical Engineers – Council Student Chapters">
            IIEE-CSC</option>
        <option value="JHSO" title="Junior High Student Organization">JHSO</option>
        <option value="JMA" title="Junior Marketing Association">JMA</option>
        <option value="SHSO" title="Senior High Student Organization">SHSO</option>
        <option value="SITS" title="Society of Industrial Technology Students">SITS</option>
        <option value="SPEAR" title="Sports Physical Education and Recreation Club">SPEAR</option>
    </optgroup>
    <optgroup label="Non-Academic Organization">
        <option value="CCERT" title="CvSU-CCAT Emergency Response Team">CCERT</option>
        <option value="NEXUS" title="The CvSU-R Nexus (Official Student Publication)">NEXUS</option>
        <option value="ROTARACT" title="Rotaract Club of CvSU-CCAT">ROTARACT</option>
        <option value="SEC" title="Sikat E-Sports Club">SEC</option>
    </optgroup>
    <optgroup label="Performing Arts Groups">
        <option value="ARTRADS">ARTRADS Dance Crew</option>
        <option value="CHORALE">CvSU-CCAT Chorale</option>
        <option value="SONIC-PISTONS">CvSU-CCAT Sonic Pistons Live Band
        </option>
    </optgroup>
    <optgroup label="Student Body Organization">
        <option value="CSG" title="Central Student Government of CvSU-CCAT">CSG
        </option>
    </optgroup>
</select>

<script>
    $(document).ready(function () {
        $('.co-organization-select').select2({
            width: 'resolve',
            placeholder: 'Select Organization',
            allowClear: true
        });
    });
</script>