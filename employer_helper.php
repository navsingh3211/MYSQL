<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('getEndpointId')) {
    function getEndpointId()
    {
        $ci = &get_instance();
        $uri = $ci->uri->uri_to_assoc(2);
        $endpoint = $ci->uri->assoc_to_uri($uri);
        $trail_endpoint = rtrim($endpoint, "/");

        $query = $ci->db->where_in('slug', [$endpoint, $trail_endpoint])->get('endpoint_master');
        if ($query->num_rows())
            return $query->row()->id;
        else
            return false;
    }
}

if (!function_exists('checkEmployerSlugAccess')) {
    function checkEmployerSlugAccess($employer_id, $endpoint_id)
    {
        $ci = &get_instance();
        $query = $ci->db->where(['employer_id' => $employer_id, 'endpoint_id' => $endpoint_id, 'status' => true])->get('employer_endpoint_accessibility');
        if ($query->num_rows())
            return true;
        else
            return false;
    }
}

if (!function_exists('isProtectedEndpoint')) {
    function isProtectedEndpoint($id)
    {

        if (!$id)
            return false;

        $ci = &get_instance();
        $endpoint = $ci->db->where('id', $id)->get('endpoint_master')->row();
        if ($endpoint->is_protected == 1)
            return true;
        else
            return false;
    }
}

if(!function_exists('is_valid_employer_id')) {
    function is_valid_employer_id($employerID) {
        if(!$employerID) return false;

        $ci =& get_instance();
        
        $ci->db->select('id');
        $ci->db->from('institution_companies');
        $ci->db->where(['status' => 1, 'id' => $employerID]);
        $result = $ci->db->get();

        return $result->num_rows() == 1 ? true : false;
    }
}

if (!function_exists('employer_existence')) {
    function employer_existence($username, $checkVerified = true)
    {
        $ci = &get_instance();
        $ci->db->select('id as employerid');
        $ci->db->from('institution_companies');
        $ci->db->where('status', 1);
        $ci->db->group_start();
        $ci->db->where('email', $username);
        $ci->db->or_where('phone', $username);
        $ci->db->group_end();
        // Check if email or phone is verified
        if ($checkVerified) {
            if (validate_email($username))
                $ci->db->where('is_verify', 1);
            else
                $ci->db->where('is_mobile_verified', 1);
        }
        $query = $ci->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return false;
    }
}

if (!function_exists('fetch_hiring_for_list')) {
    function fetch_hiring_for_list($employer_id)
    {
        $ci = &get_instance();

        //////#### Old Query Builder Code  ######
        // $query = $ci->db->select('id, institution_name')
        //                 ->where('institution_company_id', $employer_id)
        //                 ->get('institution_company_profiles');

        //////#### Raw MySQL Query for the current code  #######
        // $sql = "SELECT id, institution_name, institution_subcat_other,
        // CASE WHEN institution_subcat_other != '' THEN 'pending'
        // ELSE 'approved'
        // END AS isApproved
        // FROM institution_company_profiles
        // WHERE institution_company_id = " . $employer_id;
        // $query = $ci->db->query($sql);

        // New query using CI query builder     --Abhinandan   ---08-12-2021
        // New query using CI query builder     --Somnath Paul   ---14-01-2022
        /*$query = $ci->db->select("id, institution_name, CASE WHEN institution_subcat_other != ''  OR institution_subcat2_other != '' THEN 'pending' ELSE 'approved' END AS isApproved")*/

        $query = $ci->db->select("id, institution_name, CASE WHEN under_vetting = 1 THEN 'pending' ELSE 'approved' END AS isApproved")
            // New query using CI query builder     --Somnath Paul   ---14-01-2022 --END
            ->where('institution_company_id', $employer_id)
            ->get('institution_company_profiles');

        return $query->result_array();
    }
}

// Jitendra 02-12-2021 >>> START
if (!function_exists('isGroupCompany')) {
    function isGroupCompany($employer_id)
    {
        $ci = &get_instance();
        $query = $ci->db->where('institution_company_id', $employer_id)->get('institution_company_profiles');
        if ($query->num_rows() > 1)
            return true;
        else
            return false;
    }
}

if (!function_exists('isStandaloneCompany')) {
    function isStandaloneCompany($employer_id)
    {
        $ci = &get_instance();
        $query = $ci->db->where('institution_company_id', $employer_id)->get('institution_company_profiles');
        if ($query->num_rows() < 2 && $query->num_rows() != 0)
            return true;
        else
            return false;
    }
}

if (!function_exists('getInstituteLogo')) {
    function getInstituteLogo($id)
    {
        $ci = &get_instance();
        $query = $ci->db->where('id', $id)->get('institution_company_profiles');
        if ($query->num_rows() > 0 && $query->row()->institution_logo != '')
            return $query->row()->institution_logo;
        else
            return '';
    }
}

if (!function_exists('getCompanyLogo')) {
    function getCompanyLogo($id)
    {
        $ci = &get_instance();
        $query = $ci->db->where('id', $id)->get('institution_companies');
        if ($query->num_rows() > 0 && $query->row()->company_logo != '')
            return $query->row()->company_logo;
        else
            return '';
    }
}
// Jitendra 02-12-2021 <<< END



// removed on 16-03-2022 due to already exists with same function name under common_helper @author Jitendra
/*if(!function_exists('get_institution_category')){
    function get_institution_category_name($id){

        $ci = & get_instance();
        $query = $ci->db->select('name')->where('id',$id)->get('institution_category');
        if($query->num_rows() > 0 && $query->row()->name !='')
            return $query->row()->name;
        else
            return null;

    }
}*/


if (!function_exists('get_institution_sub_category_name')) {
    function get_institution_sub_category_name($id)
    {

        $ci = &get_instance();
        $query = $ci->db->select('name')->where('id', $id)->get('institution_subcategory');
        if ($query->num_rows() > 0 && $query->row()->name != '')
            return $query->row()->name;
        else
            return null;
    }
}


if (!function_exists('get_institution_sub_category_label')) {
    function get_institution_sub_category_label($id)
    {

        $ci = &get_instance();
        $query = $ci->db->select('label')->where('id', $id)->get('institution_subcategory');
        if ($query->num_rows() > 0 && $query->row()->label != '')
            return $query->row()->label;
        else
            return null;
    }
}



if (!function_exists('get_institution_sub_category2_name')) {
    function get_institution_sub_category2_name($id)
    {

        $ci = &get_instance();
        $query = $ci->db->select('name')->where('id', $id)->get('institution_subcategory2');
        if ($query->num_rows() > 0 && $query->row()->name != '')
            return $query->row()->name;
        else
            return null;
    }
}



if (!function_exists('get_institution_sub_category2_label')) {
    function get_institution_sub_category2_label($id)
    {

        $ci = &get_instance();
        $query = $ci->db->select('label')->where('id', $id)->get('institution_subcategory2');
        if ($query->num_rows() > 0 && $query->row()->label != '')
            return $query->row()->label;
        else
            return null;
    }
}


if (!function_exists('get_employer')) {
    function get_employer($where)
    {

        $ci = &get_instance();

            $ci->db->where($where);
            $query = $ci->db->get('institution_companies');
            if ($query->num_rows() > 0)
                return $query->row_array();
            else
                return false;
    }
}

if (!function_exists('save_login_history')) {
    function save_login_history($data)
    {
        $ci = &get_instance();
        $ci->db->insert('employer_login_history', $data);
    }
}



if (!function_exists('companyVetting')) {
    function companyVetting($employer_id)
    {
        $ci = &get_instance();
        $query = $ci->db->where('institution_company_id', $employer_id)->get('institution_company_profiles');

        $count_vett = $query->num_rows();

        $query2 =   $ci->db->where(['institution_company_id' => $employer_id, 'under_vetting' => 1])->get('institution_company_profiles');
    
        $query3 =   $ci->db->where(['institution_company_id' => $employer_id, 'under_vetting' => null])->get('institution_company_profiles');

        #all the institutions on vetting
        $query6 =   $ci->db->where(['institution_company_id' => $employer_id,])
                    ->group_start()
                    // ->or_where('under_vetting',3)
                    ->or_where('under_vetting',NULL)
                    ->or_where('under_vetting',1)
                    ->group_end()
                    ->get('institution_company_profiles');

        #all the institution got rejected                
        $query7 =   $ci->db->where(['institution_company_id' => $employer_id,])
                ->group_start()
                ->or_where('under_vetting',3)
                ->group_end()
                ->get('institution_company_profiles');


        // echo $count_vett; 
        // echo $ci->db->last_query(); die(' last query');

        $query4 =   $ci->db->where(['institution_company_id' => $employer_id,])->or_where('under_vetting',3)->get('institution_company_profiles');

        //$query2  = vetting |||$query3 = non vetting
        if (($query2->num_rows() == $count_vett && $count_vett == 1 && $query3->num_rows() == 0) || ($query2->num_rows() == $count_vett) && $count_vett === 0) {
            return 1;
        }else if($query4->num_rows() == $count_vett){
            # all subcategory on vetting 
            return 2;
        }
        else if($query7->num_rows() == $count_vett){
            # all subcategory on vetting
            return 5;
        }
        else if(($query6->num_rows() + $query7->num_rows()) == $count_vett){
            # some subcategory on vetting and some subcategory on rejected 
            return 2;
        }
         elseif ($query2->num_rows() == $count_vett) {
            # code...
            return 2;
        } elseif ($count_vett > $query2->num_rows() && $query3->num_rows() == 0) {

            return 3;
            # code...
        } else {
            return 4;
        }
    }
}

if (!function_exists('companyinstituteexists')) {
    function companyinstituteexists($employer_id)
    {

        $ci = &get_instance();
        $query = $ci->db->where('institution_company_id', $employer_id)->get('institution_company_profiles');

        $count_vett = $query->num_rows();

        if ($count_vett == 0) {
            # code...
            return false;
        } else {
            return true;
        }
    }
}

// Function to check if institutionProfile ID is valid --Abhinandan  --13-01-2022 --Start
if (!function_exists('ins_profile_exists')) {
    function ins_profile_exists($ins_id, $employer_id)
    {
        if ($ins_id && $employer_id) {
            $ci = &get_instance();
            $ci->db->where('id', $ins_id);
            $ci->db->where('institution_company_id', $employer_id);
            $ci->db->where('status', 1);
            $query = $ci->db->get('institution_company_profiles');
            return $query->num_rows() > 0 ? true : false;
        } else {
            return false;
        }
    }
}
// Function to check if institutionProfile ID is valid --Abhinandan  --13-01-2022 --End

// Created By Jit 18-01-2022 || Jit modified on 01-04-2022
if (!function_exists('get_employer_domain')) {
    function get_employer_domain($employer_id, $instituteId = null)
    {
        $ci = &get_instance();
        $ci->db->select("
            id as instituteId,
            institution_name as instituteName,
            institution_logo as instituteLogo,
            institution_cat as institutionCat,
            (SELECT DISTINCT name FROM view_institution_category_for_institute_job_post WHERE id = institutionCat) AS institutionCatName,
            institution_subcat as institutionSubCat,
            (SELECT DISTINCT name FROM view_institution_subcategory_for_institute_job_post WHERE id = institutionSubCat) AS institutionSubCatName,
            institution_subcat_other as institutionSubCatOther,
            institution_subcat2 as institutionSubCat2,
            (SELECT DISTINCT name FROM view_institution_subcategory2_for_institute_job_post WHERE id = institutionSubCat2) AS institutionSubCat2Name,
            institution_subcat2_other as institutionSubCat2Other,
            city,
            CASE WHEN under_vetting = 1 THEN 'pending' ELSE 'approved' END AS isApproved
            ");
        $ci->db->where('institution_company_id', $employer_id);
        if( $instituteId){
            $ci->db->where('id', $instituteId);
        }
        $ci->db->where('status', 1);
        $ci->db->order_by('instituteName', 'ASC');
        $query = $ci->db->get('institution_company_profiles');
        return $query->result_array();
    }
}

// Function to fetch the profile picture of candidate  --Abhinandan  --19-01-2022 --Start
if (!function_exists('getS3img')) {
    function getS3img($seekerID)
    {
        $ci = &get_instance();
        // require_once APPPATH . 'libraries/aws/aws-autoloader.php';
        $ci->config->load('s3', TRUE);
        $s3config = $ci->config->item('s3');
        $ci->s3 = new Aws\S3\S3Client($s3config['sharedConfig']);
        $bucket = 'jie-employee-profile';
        // $seekerFolder = get_seeker_info($seekerID)['seekerid'];
        $key = $seekerID . '/profiledp';
        $cmd = $ci->s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $key
        ]);
        if ($ci->s3->doesObjectExist($bucket, $key)) {  //Navneet 23-01-2022
            $request = $ci->s3->createPresignedRequest($cmd, '+30 minutes');
            // Get the actual presigned-url
            $presignedUrl = (string)$request->getUri();
            return  $presignedUrl;
        } else {
            return null;
        }
    }
}
// Function to fetch the profile picture of candidate  --Abhinandan  --19-01-2022 --End

// function to get the profile of Employer from aws server   Navneet 24-02-2022 Start>>>
//$uploaderKey will be 'companylogo' if we require company logo and
// will 'institutionlogo_.$institutionId' if we require institution logo
if (!function_exists('getInstitutionS3img')) {
    function getInstitutionS3img($employerId, $uploaderKey)
    {
        $ci = &get_instance();
       // require_once APPPATH . 'libraries/aws/aws-autoloader.php';

        $ci->config->load('s3', TRUE);
        $s3config = $ci->config->item('s3');
        $ci->s3 = new Aws\S3\S3Client($s3config['sharedConfig']);
        $filekey = "employer/profile/logo/" . $employerId . "/" . $uploaderKey;
        $bucket = 'jie-employee-profile';
        $key = $filekey;
        $cmd = $ci->s3->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $key
        ]);
        $error = null;
        ///check if object exists
        $response = $ci->s3->doesObjectExist($bucket, $key);
        //var_dump($response); die;
        if ($response) //i.e. if the image really exists then pull the data
        {
            $request = $ci->s3->createPresignedRequest($cmd, '+30 minutes');
            // Get the actual presigned-url
            $presignedUrl = (string)$request->getUri();
            return $presignedUrl;
        } else //return blank string if not present
        {
            return null;
        }
    }
}
// function to get the profile of Employer from aws server   Navneet 24-02-2022 END<<<<
//Get employerid in varchar when we are gaving a int value of employer  Navneet 24-02-2022 START>>>
if (!function_exists('getVarChar_formateEmployerId')) {
    function getVarChar_formateEmployerId($employerId)
    {
        $ci = &get_instance();
        $ci->db->select('employerid');
        $ci->db->from('institution_companies');
        $ci->db->where('id', $employerId);
        $sql = $ci->db->get()->row_array();
        return $sql;
    }
}
//function to get index number of a institution To get institutionLogo from aws server
if (!function_exists('getIndexno')) {
    function getIndexno($employerId, $institution_id)
    {
        $ci = &get_instance();
        $ci->db->select('id');
        $ci->db->from('institution_company_profiles');
        $ci->db->where('institution_company_id', $employerId);
        $ci->db->where('status', 1);
        $institutes = $ci->db->get()->result_array();
        // /print_r($institutes);die;
        foreach ($institutes as $key => $institute) {
            if ($institute['id'] == $institution_id) {
                return $key + 1;
            }
        }
    }
}

// //Get employerid in varchar when we are gaving a int value of employer
// // helper to get job details posted by employer
// if (!function_exists('getJobDetailsEmployer')) {
//     function getJobDetailsEmployer($employerId, $job_id)
//     {
//         $ci = &get_instance();
//         $jobDetails = array();
//         $ci->db->select("
//             jobs.id as jobId,
//             jobs.for_entire_group as forEntireGroup,
//             jobs.min_experience as experience,
//             jobs.description as jobDescription,
//             CASE
//             WHEN jobs.customtitle is NULL OR jobs.customtitle='' then jobs.title ELSE jobs.customtitle
//             END AS jobTitle,
//             function_get_EmploymentTypeName(jobs.employment_type) as employmentType,
//             jobs.max_salary as salary,
//             function_get_CityName(vacancy.city) as cityName,
//             function_get_StateName(vacancy.state) as stateName,
//             (
//                 SELECT type
//                 FROM `education` AS `education`
//                 WHERE `education`.`id` = `jobs`.`min_education`
//             ) AS educationName,
//             vacancy.total_positions as vacancies,
//             profile.institution_name as instituteName,
//             profile.id as profileId,
//             function_get_CityName(profile.city) as instituteCityName
//         ");
//         $ci->db->from('job_post as jobs');
//         $ci->db->join('institution_company_profiles as profile', 'profile.id=jobs .institute_profile_id');
//         $ci->db->join('job_post_vacancies as vacancy', 'vacancy.job_id=jobs.id', 'left');
//         $ci->db->where('jobs.employer_id', $employerId);
//         $ci->db->where('jobs.id', $job_id);
//         $ci->db->where_in('jobs.status', [-1, 1, 2]);
//         $ci->db->where('jobs.end_of_life_date >= ', 'UTC_TIMESTAMP()', false);
//         $jobs = $ci->db->get()->row_array();
//         //skills
//         $ci->db->select('skill_name');
//         $ci->db->from('job_post_skill');
//         $ci->db->where('job_id', $jobs['jobId']);
//         $skills = $ci->db->get()->result_array();
//         $jobs['skills'] = $skills;
//         //companyLogo generation
//         $employer_id = getVarChar_formateEmployerId($employerId)['employerid'];
//         $uploaderKeyforcompanyLogo = 'companylogo';
//         $companyLogo = getInstitutionS3img($employer_id, $uploaderKeyforcompanyLogo);
//         $jobs['companyLogo'] = $companyLogo;
//         //institutionLogo generation
//         // $index=getIndexno($employerId,$jobs['profileId']);
//         $uploaderKeyforinstitutionLogo = 'institutionlogo_' . $jobs['profileId'];
//         $institutionLogo = getInstitutionS3img($employer_id, $uploaderKeyforinstitutionLogo);

//         $jobs['institutionLogo'] = $jobs['forEntireGroup'] == 0 ? $institutionLogo : null;
//         $jobs['institutionLogo'] = $institutionLogo;
//         return $jobs;
//     }
// }
// ///   Navneet 25-02-2022 END<<<<<



/* Employer name - pravat - 28-01-2022 - start */
if (!function_exists('getEmployerName')) {
    function getEmployerName($employerId)
    {
        $ci = &get_instance();
        $ci->db->select('
            company_name AS companyName
        ');
        $ci->db->from('institution_companies');
        $ci->db->where('id', $employerId);
        $ci->db->or_where('employerid', $employerId);
        $sql = $ci->db->get();
        if ($sql->num_rows()) {
            return $sql->row()->companyName;
        } else
            return null;
    }
}

// get employer info
if (!function_exists('get_employer_info')) {
    function get_employer_info($id)
    {
        $ci = &get_instance();
        $ci->db->select('
            *
        ');
        $ci->db->from('institution_companies');
        $ci->db->where('id', $id);
        $ci->db->where("status", 1);
        $query = $ci->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return false;
    }
}
/* Empoyer name - pravat - 28-01-2022 - end */

//Get employer company profile details
if (!function_exists('getEmployerCompanyProfile')) {
    function getEmployerCompanyProfile($id)
    {
        $ci = &get_instance();
        $ci->db->select("
            id,
            Institution_name as name,
            city
        ");
        $ci->db->where('institution_company_id', $id);
        $ci->db->where("status", 1);
        $query = $ci->db->get('institution_company_profiles');
        return $query->result_array();
    }
}

//Get employer company profile Name
if (!function_exists('getEmployerCompanyProfileName')) {
    function getEmployerCompanyProfileName($id)
    {
        $ci = &get_instance();
        $ci->db->select("
                institution_name as name
        ");
        // $ci->db->where('institution_company_id',$id);
        $ci->db->where('id', $id); //dharmesh
        //$ci->db->where("status !=",null);
        $query = $ci->db->get('institution_company_profiles');
        if ($query->num_rows()) {
            return $query->row()->name;
        } else
            return null;
    }
}

//Get employer company profile Name
if (!function_exists('getEmployerCompanyProfileRefCode')) {
    function getEmployerCompanyProfileRefCode($id, $name)
    {
        $ci = &get_instance();
        $ci->db->select("ref_code");
        // $ci->db->where('institution_company_id',$id);
        $ci->db->where('id', $id); // dharmesh
        $ci->db->where('institution_name', $name);
        //$ci->db->where("status !=",null);
        $query = $ci->db->get('institution_company_profiles');
        if ($query->num_rows()) {
            return $query->row()->ref_code;
        } else
            return null;
    }
}

// Get ref_code from any table --Abhinandan --14-02-2022 --Start
if (!function_exists('get_ref_code')) {
    function get_ref_code($table_name, $primaryID)
    {
        $query = false;
        if ($table_name && $primaryID) {
            $ci = &get_instance();
            $query = $ci->db->select('ref_code')
                ->where('id', $primaryID)
                ->where('status !=', NULL)
                ->get($table_name)->row_array();

            if (!empty($query)) {
                $query = array_key_exists('ref_code', $query) ? $query['ref_code'] : false;
            }
        }
        return $query;
    }
}
// Get employer ref_code --Abhinandan --14-02-2022 --End

// Get original job_id if valid job --Abhinandan --16-02-2022 --Start
if (!function_exists('get_job_original_id')) {
    function get_job_original_id($employer_id, $job_id)
    {
        if (!$job_id) {
            return null;
        }

        $ci = &get_instance();

        # Added ability to use both job_id or ref_code in API parameter --23-02-2022
        $prefix = substr($job_id, 0, 4);
        $key = ($prefix == 'JBID') || ($prefix == 'PJID') ? 'ref_code' : 'job_id';

        $query = $ci->db->select('id')
                        ->where('employer_id', $employer_id)
                        ->where('status !=', NULL)
                        ->where('end_of_life_date >', 'UTC_DATE()', FALSE)
                        ->where($key, $job_id)
                        ->get('job_post');
        
        return $query->num_rows() ? $query->row_array()['id'] : null;
    }
}
// Get original job_id if valid job --Abhinandan --16-02-2022 --End

// Get original job_id if valid job --Abhinandan --16-02-2022 --Start
if (!function_exists('get_verified_job_id')) {
    function get_verified_job_id($employer_id, $job_id)
    {
        if (!$job_id) {
            return null;
        }

        $ci = &get_instance();

        # Added ability to use both job_id or ref_code in API parameter --23-02-2022
        $prefix = substr($job_id, 0, 4);
        $key = ($prefix == 'JBID') || ($prefix == 'PJID') ? 'ref_code' : 'job_id';

        $query = $ci->db->select('id')
            ->where('employer_id', $employer_id)
            ->where_in('status',[-1,1,2])
            ->where($key, $job_id)
            ->get('job_post')->row_array();

        return $query ? $query['id'] : null;
        // return !empty($query) ? (array_key_exists('id', $query) ? $query['id'] : null) : null;
    }
}
// Get original job_id if valid job --Abhinandan --16-02-2022 --End

// Abhinandan  >>>> Get original private job_id if valid -27-02-2022 --Start
if (!function_exists('get_original_PJID')) {
    function get_original_PJID($job_id, $employer_id)
    {
        $ci = &get_instance();
        $prefix = substr($job_id, 0, 4);
        $key = ($prefix == 'PJID' || $prefix == 'JBID') ? 'ref_code' : 'job_id';

        $ci->db->select('id');
        $ci->db->where($key, $job_id);
        $ci->db->group_start();
        $ci->db->where('is_private', 1);
        $ci->db->or_where('status', -1);
        $ci->db->group_end();
        $ci->db->where('employer_id', $employer_id);
        $result = $ci->db->get('job_post')->row_array();

        return $result != null ? (array_key_exists('id', $result) ? $result['id'] : null) : null;
    }
}
// Abhinandan  >>>> Get original private job_id if valid -27-02-2022 --End

// Function to check if all data exists for a  private job --Abhinandan --22-02-2022 --Start
if (!function_exists('check_privateJob_data')) {
    function check_privateJob_data($job_id)
    {
        $mandatory = ['title', 'min_work_experience', 'max_salary', 'employment_type', 'country', 'city', 'job_description', 'min_education'];
        $optional = ['skills'];
        $incomplete = [];

        $ci = &get_instance();

        $ci->db->select('
                    title,
                    min_experience,
                    min_education,
                    max_salary,
                    employment_type,
                    description
                    ');
        $ci->db->from('job_post');
        $ci->db->where('id', $job_id);
        $data = $ci->db->get()->row_array();

        foreach ($data as $key => $value) {
            if ($value == NULL || $value == "NULL" || $value == "" || $value === 0) {
                array_push($incomplete, $key);
            }
        }

        # Insert city obj
        $ci->db->select('
    vacancy.city AS id,
    (SELECT name FROM xx_cities WHERE id = vacancy.city) As name
    ');
        $ci->db->from('job_post_vacancies AS vacancy');
        $ci->db->where('job_id', $job_id);
        $ci->db->where('status', 1);
        $city = $ci->db->get()->row_array();

        $data['city'] = $city;
        if (empty($city)) {
            array_push($incomplete, 'city');
        }
        // <-- End of cities obj

        # Insert country obj
        $ci->db->select('
    country AS id,
    (SELECT name FROM xx_countries WHERE id = country) AS name
    ');
        $ci->db->from('job_post_vacancies');
        $ci->db->where('job_id', $job_id);
        $ci->db->where('status', 1);
        $country = $ci->db->get()->row_array();

        $data['country'] = $country;
        if (empty($country)) {
            array_push($incomplete, 'country');
        }
        // <-- End of country obj


        # Insert skill details
        $skills = $ci->db->select('skill_id, skill_name')
            ->from('job_post_skill')
            ->where('job_id', $job_id)
            ->where('status', 1)
            ->get()->result_array();

        $data['skills'] = $skills;
        // <--- End of skills

        return array('job_details' => $data, 'incomplete' => $incomplete);
    }
}
// Function to check if all data exists for a  private job --Abhinandan --22-02-2022 --End

// ##Abhinandan - Get resume of a candidate --28-02-2022 --Start
if (!function_exists('getS3resume')) {
    function getS3resume($seekerID)
    {
        //---
        return null;
        //---
        $ci = &get_instance();
        // require_once APPPATH . 'libraries/aws/aws-autoloader.php';
        $ci->config->load('s3', TRUE);
        $s3config = $ci->config->item('s3');
        $ci->s3 = new Aws\S3\S3Client($s3config['sharedConfig']);

        $bucket = "jie-employee-profile";
        $key = $seekerID . "/resume";

        # Check if the following seeker's data exists
        if ($ci->s3->doesObjectExist($bucket, $key)) {
            $cmd = $ci->s3->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key
            ]);

            $request = $ci->s3->createPresignedRequest($cmd, '+30 minutes');
            $presignedUrl = (string)$request->getUri();
            return $presignedUrl;
        } else {
            return null;
        }
    }
}
// ##Abhinandan - Get resume of a candidate --28-02-2022 --End

# Abhinandan - Check if a job is private --01-03-2022 --Start
if (!function_exists('is_private_job')) {
    function is_private_job($job_id)
    {
        if ($job_id == null) {
            return false;
        }
        $ci = &get_instance();

        $query = $ci->db->select('id')
            ->where('id', $job_id)
            ->group_start()
            ->where('is_private', 1)
            ->or_where('status', -1)
            ->group_end()
            ->get('job_post')->result_array();

        return !empty($query) ? true : false;
    }
}
# Abhinandan - Check if a job is private --01-03-2022 --End

##Navneet Func to get seeker_id from preference_id 18-05-2022 Start>>
if (!function_exists('get_seeker_id_By_reference_id')) {
    function get_seeker_id_By_reference_id($preference_id)
    {
        $ci = &get_instance();

        $ci->db->select('seeker_id');
        $ci->db->from('seeker_jobpreference');
        $ci->db->where('id', $preference_id);
        // $ci->db->where('status', 1);
        // $ci->db->where_in('status', [1, -2, -1]); // dharmesh
        $seeker_id = $ci->db->get()->row_array();
        return !empty($seeker_id) ? $seeker_id['seeker_id'] : null;
    }
}
##Navneet Func to get seeker_id from preference_id 18-05-2022 END<<

if (!function_exists('get_seeker_id_By_preference_id_forSearch')) {
    function get_seeker_id_By_preference_id_forSearch($preference_id)
    {
        $ci = &get_instance();

        $ci->db->select('seeker_id');
        $ci->db->from('seeker_jobpreference');
        $ci->db->where('id', $preference_id);
        $ci->db->where_in('status', [1, -2, -1]);
        $seeker_id = $ci->db->get()->row_array();
        //print_r($seeker_id) ; die;
        return !empty($seeker_id) ? $seeker_id['seeker_id'] : null;
    }
}

## Candidate List generic helper for employer --Navneet 22-04-2022 START>>>----##
function get_candidate_card_generic_employer($candidate_pref_arr, $jobId, $fetchfor = ['invited', 'saved', 'applied', 'suggested', 'shortlisted', 'selected', 'onhold', 'underprocess', 'default'], $jobObj = [])
{

    $ci = &get_instance();
    $priority = DEFAULT_PRIORITY_FOR_SEARCH_SUGGESTED_CANDIDATES;
    //fetchfor convert to lower case
    foreach ($fetchfor as $key => $value) {
        $fetchfor[$key] = strtolower($value);
    }
    // print_r($fetchfor);die;
    $finalCandidateArr = array();
    if (!empty($candidate_pref_arr)) {
        foreach ($candidate_pref_arr as $key => $preference_id) {
            $seekerId = get_seeker_id_By_reference_id($preference_id);
            
            // print_r($seekerId);die;
            /// SELECT PART
            $sql = "select
                    seeker.seekerid AS seekerId,
                    seeker.id AS seekerIdReal,
                    seeker.ref_code AS refCode,
                    IF(unlocked.id IS NOT NULL,seeker.photo,seeker.blurred_photo) AS img,
                    preference.id AS seekerPreferenceId,
                    IF(seeker.resume IS NOT NULL, 1, 0) AS hasResume,
                    IF((SELECT (REPLACE(system_title, '\t', '')) FROM job_post WHERE id = " . $jobId . ") = (SELECT (REPLACE(system_title, '\t', '')) FROM seeker_jobpreference WHERE id = " . $preference_id . "), 1, 0) AS is_perfectmatch,
                    IF(unlocked.created_on,unlocked.created_on,NULL) AS unlockedDate,
                   ";
            if (in_array('applied', $fetchfor, true) || in_array('shortlisted', $fetchfor, true) || in_array('selected', $fetchfor, true) || in_array('onhold', $fetchfor, true) || in_array('underprocess', $fetchfor, true)) {
                $sql .= " TRIM(CONCAT(IFNULL(seeker.first_name,' '), ' ',IFNULL(seeker.last_name,' '))) as candidateName,
                        CONCAT('{\"candidateMobile\":\"',seeker.mobile,' \", \"candidateEmail\": \"',seeker.email,'\"}') AS contact,
                        NULL AS lockedMessage,
                        IF(unlocked.id IS NULL, 1, 0) AS locked,
                        ";
            } else {
                $sql .= " CASE
                            WHEN unlocked.id IS NULL THEN 'Unlock To View Candidate Name'
                            ELSE CONCAT(seeker.first_name, ' ', seeker.last_name)
                        END AS candidateName,
                        IF(unlocked.id IS NULL, NULL, CONCAT('{\"candidateMobile\":\"',seeker.mobile,'\", \"candidateEmail\": \"',seeker.email,'\"}')) AS contact,
                        IF(unlocked.id IS NULL, 1, 0) AS locked,
                        CASE
                            WHEN unlocked.id IS NULL THEN 'Unlock To View Candidate Name'
                            ELSE NULL
                        END AS lockedMessage,
                        ";
            }

            $sql .= "
                    function_get_CityName(seeker.city) AS candidateCity,
                    function_get_higherEducation(seeker.highest_education) AS highestEducation,
                    (
                        SELECT
                            IF(
                                specialization_isother = 1, 
                                specialization_other, 
                                function_get_higherEducation_specialisationName(seeker_id, seeker.highest_education)
                            )
                        FROM
                            seeker_education
                        WHERE
                            seeker_id = seeker.id
                            AND education_id = seeker.highest_education
                            AND `status` = 1
                    ) AS educationSpecialization,
                    function_get_experience(preference.experience ) AS maxExperience,
                    function_get_seeker_max_notice_period_val(preference.seeker_id) AS maxNoticePeriod,
                    function_get_seeker_last_edited(preference.id) AS lastEdited,
                    function_get_seeker_last_activity(preference.id) AS lastActive,
                    seeker.last_active AS lastActive_old,
                    IFNULL(concat('',DATEDIFF(UTC_TIMESTAMP,function_get_seeker_last_activity(preference.id)),' '),'') as lastActiveIn,
                    IF((SELECT count(job_id) FROM employer_employee_comments WHERE seeker_id = seeker.id AND status = 1 AND job_id = " . $jobId . ") > 0, TRUE, FALSE) AS hasComments,
                    function_get_search_has_candidate_acted_upon(seeker.id," . $jobId . ")AS isActedUpon,
                    JSON_EXTRACT(function_get_search_last_activity_message(preference.id," . $jobId . "),'$.message') AS actedUponMessage,
                    function_candidate_is_actively_searching(seeker.id) AS isActivelySearching,
                    CASE
                        WHEN function_get_candidate_isFresher(seeker.id)=1 THEN TRUE
                        WHEN function_get_candidate_isFresher(seeker.id)=0 THEN FALSE
                        ELSE NULL
                    END AS isFresher2,
                    CASE
                        WHEN seeker.is_experienced=1 THEN 0
                        WHEN seeker.is_experienced=0 THEN 1
                        ELSE NULL
                    END AS isFresher,
                    CASE
                        WHEN function_get_candidates_is_saveable(" . $jobId . ",preference.id) = 1 THEN TRUE
                        ELSE FALSE
                    END AS isSavable,
                    CASE
                        WHEN function_getSeekerIsActive(preference.id) = 1 THEN TRUE
                        ELSE FALSE
                    END AS isActive
                    ";

            ## Extra KEYS for invited section
            if (in_array('default', $fetchfor, true) || in_array('invited', $fetchfor, true)) {
                // $active_organizations = '(SELECT COUNT(seeker_id) FROM seeker_experience WHERE seeker_id = invited.seeker_id AND current_company = "Yes" AND status = 1)';
                $sql .= ",
                    invited.id AS invitedID,
                    invited.is_viewed as isViewed,
                    invited.created_on AS invitedDate,
                    IF(
                        (SELECT COUNT(id) FROM employer_invited_employee WHERE seeker_id = " . $seekerId . " AND job_id = " . $jobId . " AND `status` = 1) > 0, 
                        1, 
                        0
                      ) AS hasInvited,
                    applied.created_on AS appliedDate,
                    applied.id AS invitedAppliedId
    
                ";
            } else {
                $sql .= ", NULL AS invitedID,
                        NULL AS invitedDate,
                        NULL AS hasInvited,
                        NULL AS appliedDate,
                        NULL AS invitedAppliedId
    
                ";
            }

            
            ## Extra KEYS for saved section
            if (in_array('default', $fetchfor, true) || in_array('saved', $fetchfor, true)) {                
                $sql .= ",
                        saved.id AS savedId,
                        IF(saved.id IS NOT NULL, 1, 0) AS hasSaved,
                        saved.created_on AS savedDate,
                            applied.id as appliedId,
                        applied.is_viewed as isViewed,
                        applied.applied_date as appliedDate,
                        IF(applied.id IS NOT NULL, 1, 0) AS hasApplied
                ";
            } else {
                    $sql .= ", NULL AS savedId,
                            NULL AS hasSaved,
                            NULL AS savedDate
                        ";
            }
            # end of saved section keys

            
            ## Extra keys for applied section
            if (in_array('default', $fetchfor, true) || in_array('applied', $fetchfor, true)) {
                $sql .= ", applied.id as appliedId,
                        applied.is_viewed as isViewed,
                        applied.applied_date as appliedDate,
                        IF(applied.id IS NOT NULL, 1, 0) AS hasApplied
                ";
            } else {
                if ((!in_array('invited', $fetchfor, true)) && (!in_array('saved', $fetchfor, true))) {
                    $sql .= ", NULL AS appliedId,
                            NULL AS isViewed,
                            NULL AS appliedDate,
                            NULL AS hasApplied
                        ";
                }
            }



            ## Extra keys for suggested section
            if (in_array('default', $fetchfor, true) || in_array('suggested', $fetchfor, true)) {
                $sql .= "";
            }

            ## Extra keys for shortlisted section
            if (in_array('default', $fetchfor, true) || in_array('shortlisted', $fetchfor, true) || in_array('onhold', $fetchfor, true)) {
                $sql .= ",  shortlisted.id AS shortlistedId,
                            shortlisted.created_on AS shortlistedDate,
                            IF(shortlisted.id IS NOT NULL, 1, 0) AS hasShortlisted
    
                ";
            } else {
                $sql .= ",  NULL AS shortlistedId,
                            NULL AS shortlistedDate,
                            NULL AS hasShortlisted
                ";
            }

            ## Extra keys for selected  section
             // selected.offer_letter_sent_on AS offerDate,
            if (in_array('default', $fetchfor, true) || in_array('selected', $fetchfor, true)) {
                $sql .= ",    selected.selectedId AS selectedId,
                            selected.is_offered AS hasOffered,
                            IF(selected.offer_letter_revise_on IS NOT NULL,selected.offer_letter_revise_on,selected.offer_letter_sent_on) AS offerDate,
                            selected.offer_deadline AS offerExpireDate,
                            selected.is_offer_accepted AS hasOfferAccepted,
                            selected.accepted_on AS offerAcceptedDate,
                            selected.is_offer_rejected AS hasOfferRejected,
                            selected.rejected_on AS offerRejectedDate,
                            selected.created_on AS selectedDate,
                            function_get_has_candidate_selected(seeker.id," . $jobId . ") AS hasSelected,
                            selected.is_offer_cancelled AS hasOfferCancelled,
                            selected.cancelled_on AS offerCancelledDate,
                            selected.cancellation_remarks AS cancellationRemarks
                ";
            } else {
                $sql .= ",  NULL  AS selectedId,
                            NULL  AS hasOffered,
                            NULL AS offerDate,
                            NULL AS offerExpireDate,
                            NULL AS hasOfferAccepted,
                            NULL AS offerAcceptedDate,
                            NULL AS hasOfferRejected,
                            NULL AS offerRejectedDate,
                            NULL AS selectedDate,
                            NULL AS hasSelected,
    
                            NULL AS hasOfferCancelled,
                            NULL AS offerCancelledDate,
                            NULL AS cancellationRemarks
    
    
                ";
            }

            ## Extra keys for onhold  section
            if (in_array('default', $fetchfor, true) || in_array('onhold', $fetchfor, true)) {
                $sql .= ",  shortlisted.is_hold AS hasOnHold,
                            holded.created_on  AS onHoldDate,
                            NULL AS onHoldSince    
                ";
            } else {
                $sql .= ",  NULL AS hasOnHold,
                            NULL AS onHoldDate,
                            NULL AS onHoldSince    
                ";
            }

            /// Data Store, From Part
            $sql .= " FROM (SELECT * FROM jobseeker WHERE id = '" . $seekerId . "') AS seeker
            LEFT OUTER JOIN employee_contact_revealed AS unlocked on unlocked.seeker_id = seeker.id  AND unlocked.status = 1 AND unlocked.job_id = " . $jobId . "
            LEFT OUTER JOIN seeker_jobpreference AS preference on seeker.id = preference.seeker_id AND preference.id = " . $preference_id . "
            LEFT OUTER JOIN seeker_experience as experience 
                        ON
                        experience.seeker_id = (CASE
                                                    WHEN experience.current_company = 'Yes'
                                                        THEN seeker.id
                                                    ELSE
                                                        (seeker.id AND experience.current_company= 'Yes' )
                                                END
                                                )
            ";

            ## Extra joining for invited section
            if (in_array('default', $fetchfor, true) || in_array('invited', $fetchfor, true)) {
                // $sql.=" LEFT OUTER JOIN employer_invited_employee AS invited on invited.seeker_id = seeker.id
                //         LEFT OUTER JOIN seeker_applied_job AS applied ON applied.seeker_id = seeker.id AND applied.job_id = '. $jobId .' AND (applied.is_shortlisted != 1 AND applied.is_rejected != 1 AND applied.status = 1 AND applied_through = 2)

                //     ";
                $sql .= " LEFT OUTER JOIN employer_invited_employee AS invited on invited.seeker_id = seeker.id AND invited.job_id = " . $jobId . " AND invited.is_shortlisted = 0 AND invited.is_removed = 0 AND invited.`status` = 1 
                        LEFT OUTER JOIN seeker_applied_job AS applied ON applied.seeker_id = seeker.id AND applied.job_id = " . $jobId . " AND (applied.is_shortlisted != 1 AND applied.is_rejected != 1 AND applied.status = 1)
                
                    ";
            }
            ## Extra joining for saved section
            if (in_array('default', $fetchfor, true)) {
                $sql .= " LEFT OUTER JOIN 
                        employer_saved_employee AS saved 
                        on saved.seeker_id=seeker.id
                        AND saved.job_id = " . $jobId . " 
                        AND saved.`status` = 1
                    ";
            }

            if (in_array('saved', $fetchfor, true)) {
                $sql .= " LEFT OUTER JOIN employer_saved_employee AS saved on saved.seeker_id = ".$seekerId." AND saved.job_id = ".$jobId." 
                        LEFT OUTER JOIN seeker_applied_job AS applied on applied.seeker_id = ".$seekerId." AND applied.job_id = ".$jobId." AND applied.`status` = 1
                ";
            }
            ## Extra joining for applied section
            if (in_array('applied', $fetchfor, true)) {
                //  $sql.=" LEFT OUTER JOIN s eeker_applied_job AS applied son applied.seeker_id = seeker.id
                //          LEFT OUTER JOIN job_post AS job on job.id = applied.job_id

                //          ";

                // 	  $sql.=" LEFT OUTER JOIN seeker_applied_job AS applied on applied.seeker_id = seeker.id
                //   LEFT OUTER JOIN job_post AS job on job.id = applied.job_id

                // 	  ";
                $sql .= " LEFT OUTER JOIN seeker_applied_job AS applied on applied.seeker_id = seeker.id
                      INNER JOIN job_post AS job on job.id = applied.job_id";
            }
            ## Extra joining for suggested section
            if (in_array('default', $fetchfor, true) || in_array('suggested', $fetchfor, true)) {
                $sql .= "
                ";
            }
            ## Extra joining for shortlisted section
            if (in_array('shortlisted', $fetchfor, true)) {
                $sql .= " LEFT OUTER JOIN employee_shortlisted AS shortlisted on shortlisted.seeker_id = seeker.id";
            }

            ## Extra joining for selected section
            if (in_array('default', $fetchfor, true)) {
            //     $sql .= " LEFT OUTER JOIN employee_shortlisted AS shortlisted ON shortlisted.seeker_id = seeker.id AND shortlisted.job_id = " . $jobId . " AND shortlisted.status = 1 AND shortlisted.is_selected = 0 AND shortlisted.is_hold=0 AND shortlisted.is_rejected=0
            //           LEFT JOIN employee_selected AS selected on selected.shortlist_id = shortlisted.id
            //   ";
              $sql .= " LEFT OUTER JOIN employee_shortlisted AS shortlisted ON shortlisted.seeker_id = seeker.id AND shortlisted.job_id = " . $jobId . " AND shortlisted.status = 1
              LEFT JOIN employee_selected AS selected on selected.shortlist_id = shortlisted.id
                ";
            }

            ## Extra joining for selected section
            if (in_array('selected', $fetchfor, true)) {
                $sql .= " LEFT OUTER JOIN employee_shortlisted AS shortlisted on shortlisted.seeker_id = seeker.id
                        INNER JOIN employee_selected AS selected on selected.shortlist_id = shortlisted.id
                ";
            }


            ## Extra joining for hold section
            if (in_array('default', $fetchfor, true) || in_array('onhold', $fetchfor, true)) {
                // $sql.=" LEFT OUTER JOIN employee_shortlisted AS shortlisted on shortlisted.seeker_id = seeker.id ";

                if (!in_array('default', $fetchfor, true)) {
                    $sql .= " LEFT OUTER JOIN employee_shortlisted AS shortlisted on shortlisted.seeker_id = seeker.id ";
                    $sql .= " INNER JOIN employee_holded AS holded on holded.shortlist_id = shortlisted.id ";
                } else {
                    $sql .= " LEFT OUTER JOIN employee_holded AS holded on holded.shortlist_id = shortlisted.id";
                }
            }

            /// Where Part
            ## WHERE clause for invited section
            if (in_array('invited', $fetchfor, true)) {
                $sql .= " WHERE invited.status = 1
                        AND invited.is_shortlisted=0
                        AND invited.is_removed=0
                        AND invited.job_id = " . $jobId . " ";
            }
            ## where clause for saved section
            if (in_array('saved', $fetchfor, true)) {
                $sql .= " and saved.status = 1
                        and saved.job_id = " . $jobId . "
    
                        ";
            }
            ## where clause form applied section
            if (in_array('applied', $fetchfor, true)) {
                $sql .= " and applied.job_id = " . $jobId . "
						and applied.status = 1
                        and applied.is_shortlisted = 0
                        and applied.is_rejected = 0
                        and applied.is_interviewed = 0
               ";
            }
            ## where clause form suggested section
            if (in_array('suggested', $fetchfor, true)) {
                $sql .= "";
            }
            ## where clause form shortlisted section
            if (in_array('shortlisted', $fetchfor, true)) {
                $sql .= " and shortlisted.job_id = " . $jobId . "
                        and shortlisted.status = 1 
                        and shortlisted.is_selected = 0
                        and shortlisted.is_hold=0
                        and shortlisted.is_rejected=0
                        ";
            }

            ## where clause form selected section
            if (in_array('selected', $fetchfor, true)) {
                $sql .= "   and shortlisted.job_id = " . $jobId . "
                            and shortlisted.is_selected = 1
                            and selected.status = 1
                ";
            }

            ## where clause form onhold section
            if (in_array('onhold', $fetchfor, true)) {
                $sql .= "   and shortlisted.job_id = " . $jobId . "
                            and shortlisted.is_hold = 1
                            and shortlisted.status = 1 
                            and holded.status = 1
                ";
            }

            if (in_array('default', $fetchfor, true)) {
                $sql .= '
                        GROUP BY preference.id
                        ';
            }
            // echo $sql;
            $query = $ci->db->query($sql);

            $finalCandidateArr[] = $query->row_array();

            $finalCandidateArr[$key]['seeker_fcm_data'] = get_user_fcm(1, $seekerId);
        }


        foreach (array_filter($finalCandidateArr) as $index => $candidate) {

            // $finalCandidateArr[$index]['offerExpireDate'] = current_datetime_ist($finalCandidateArr[$index]['offerExpireDate']);
            // print_r(getThreadId($jobId,$candidate['seekerPreferenceId'])->thread_id);die;
            $jobIdEnc=getJobEncripy($jobId)['job_id'];
            $finalCandidateArr[$index]['seekerThredId']=!empty(getThreadId($jobIdEnc,$candidate['seekerPreferenceId'])) ? getThreadId($jobIdEnc,$candidate['seekerPreferenceId'])->thread_id : null ;
            if (array_key_exists('is_perfectmatch', $candidate)) {
                $finalCandidateArr[$index]['is_perfectmatch'] = $candidate['is_perfectmatch']  == 1 ? true : false;
            }

            # TO get profile picture of every candidates
            #if candidate is locked the show blurredPhoto else show normal photo
            // $finalCandidateArr[$index]['profilePicUrl'] =$candidate['locked']==0 ? ($candidate['photo'] == null ? null : getEmployeeS3View_CDNUrl($candidate['photo'])) :($candidate['blurred_photo'] == null ? null : getEmployeeS3View_CDNUrl($candidate['blurred_photo'])) ;
            $finalCandidateArr[$index]['profilePicUrl'] =$candidate['img'] !=null ?  getEmployeeS3View_CDNUrl($candidate['img']) : null;

            ##sanitization for invited section 
            if ($finalCandidateArr[$index]['hasInvited']) {
                $finalCandidateArr[$index]['appliedId'] = $finalCandidateArr[$index]['invitedAppliedId'];
                unset($finalCandidateArr[$index]['invitedAppliedId']);
            }

            $ci->db->flush_cache();

            ## TO get candidates skills
            $ci->db->select("
                        SS.skill_id AS skillId ,
                        (CASE 
                            WHEN SS.skill_isother=0 THEN SS.name
                            WHEN SS.skill_isother=1 THEN SS.skill_other
                            ELSE NULL
                        END) AS skillName,
                        
                        CASE
                            WHEN SS.skill_isother=0 THEN IF(SS.skill_id = JS.skill_id, 1, 0)
                            WHEN SS.skill_isother=1 THEN 0
                        END AS matched,
                        SS.skill_isother AS isCustomSkill
                        ");
            $ci->db->from('(select * from jobseeker_skill)  AS SS');
            $ci->db->join(
                'job_post_skill AS JS',
                'JS.skill_id = SS.skill_id AND JS.job_id = ' . $jobId . ' AND JS.status = 1 ',
                'LEFT'
            );
            $ci->db->where('SS.seeker_id', $candidate['seekerIdReal']);
            $ci->db->where('SS.status', 1);
            $finalCandidateArr[$index]['skills'] = $ci->db->get()->result_array();

            # Work experience object start>>
            // $workExperienceData = array();
            // $ci->db->select('
            //         id AS experienceId,
            //         title AS currentJobTitle,
            //         IFNULL(institute_name,"") AS currentCompanyName,
            //         function_get_CityName(city) AS currentCompanyCity,                    
            //         IFNULL(function_get_noticePeriod(notice_period),0) AS currentNoticePeriod
            //     ');
            // $ci->db->where('seeker_id', $candidate['seekerIdReal']);
            // $ci->db->where('current_company', 'Yes');
            // $ci->db->where('status', 1);
            // $experiences = $ci->db->get('seeker_experience')->result_array();

            # Work experience object start>>
            $ci->db->select('
                    experience.id AS experienceId,
                    experience.title AS currentJobTitle,
                    IFNULL(experience.institute_name,"") AS currentCompanyName,
                    function_get_CityName(experience.city) AS currentCompanyCity,                    
                    IFNULL(function_get_noticePeriod(experience.notice_period),0) AS currentNoticePeriod,
                    GROUP_CONCAT(expSubject.subject) AS subjects,
                    experience.current_company,
                    experience.job_category,
                    experience.institution_cat_other,
                    experience.institution_cat  
                ');
            $ci->db->from('seeker_experience as experience');
            $ci->db->join('seeker_experience_subject as expSubject','expSubject.experience_id=experience.id and expSubject.priority_order>1 AND expSubject.status=1','left');
            $ci->db->where('experience.seeker_id', $candidate['seekerIdReal']);
            $ci->db->where('experience.status', 1);
            $ci->db->group_by('experience.id'); 
            $experiences = $ci->db->get()->result_array();

            $otherDomain = null;
            $currentCompaniesCount = 0;
            foreach($experiences as $key=>$experience){
                $experiences[$key]['extraSubject']=array();
                if(count(explode (",", $experience['subjects']))>1){
                    $experiences[$key]['extraSubject']=get_seeker_subjects_in_workExperience($experience['experienceId'],$candidate['seekerIdReal'],'extraSubject') ;
                }

                if($experience['current_company']=='Yes'){
                    $currentCompaniesCount++;
                    if($experience['job_category'] == 36){
                        if($currentCompaniesCount == 1){
                            if($experience['institution_cat_other']){
                                $otherDomain =$experience['institution_cat_other'] ;
                            }
                            if($experience['institution_cat']){

                                $otherDomain=is_domain_other_sector($experience['institution_cat']);
                                $otherDomain = isset($otherDomain['name']) ? $otherDomain['name'] : null;
                            }
                        }
                        if($currentCompaniesCount > 1){
                            $otherDomain = null;   
                        }
                    }
                }

                
            }
            

            $ci->db->flush_cache();
            $workExperienceData = array(
                'numberOfActiveOrganizations' => $currentCompaniesCount,
                'maxNoticePeriod' => isset($finalCandidateArr[$index]['maxNoticePeriod']) ? $finalCandidateArr[$index]['maxNoticePeriod'] : null,
                'maxNoticePeriod_old' => function_get_max_value_from_array(array_column($experiences, 'currentNoticePeriod')),
                'currentCompanies' => $experiences,
                'currentCompanyName' => getCandidateCurrentCompanyName($candidate['seekerIdReal']) == NULL ? "" : getCandidateCurrentCompanyName($candidate['seekerIdReal']),
                'currentJobTitle' => getCandidateCurrentJobTitle($candidate['seekerIdReal']) == NULL ? "" : getCandidateCurrentJobTitle($candidate['seekerIdReal']),
                'otherDomain'=>$otherDomain
            );
            unset($finalCandidateArr[$index]['maxNoticePeriod']);
            $finalCandidateArr[$index]['workExperience'] = $workExperienceData;
            # Work experience object END<<

            #preference START>>
            $ci->db->select('
                        seeker_jobpref.id AS preferenceId,
                        function_get_EmploymentTypeName(seeker_jobpref.employment_type) AS employmentType,
                        seeker_jobpref.min_salary AS minimumSalary,
                        CASE 
                            WHEN function_hasJobParamters(' . $jobId . ')=1 
                            THEN function_get_search_matchedPercent(' . $jobId . ',seeker_jobpref.id,"' . $priority . '")
                            ELSE function_get_search_matchedPercent_keyword(job.title, "' . $priority . '", seeker_jobpref.id)
                        END AS percentMatch
                        ', FALSE);
            $ci->db->from('seeker_jobpreference AS seeker_jobpref');
            $ci->db->join('job_post AS job', 'job.id=' . $jobId . ' AND seeker_jobpref.system_title IS NOT NULL ', 'left');
            $ci->db->where('seeker_jobpref.seeker_id', $candidate['seekerIdReal']);
            $ci->db->where('seeker_jobpref.id', $candidate['seekerPreferenceId']);
            // $ci->db->where('seeker_jobpref.status', 1);
            // $ci->db->where_in('seeker_jobpref.status', [1, -1, -2]); //updated by dharmesh
            $ci->db->limit(1); //20/06/2022 error fix
            $seeker_preference = $ci->db->get()->row_array();
            // $seeker_preference =  $ci->db->get()->result_array(); //20/06/2022 error fix
            // print_r($seeker_preference);
            //  die($ci->db->get_compiled_select());
            // echo $ci->db->last_query(); die;

            $ci->db->flush_cache();

            $ci->db->select("city.city AS cityId , function_get_CityName(city.city) as cityName");
            $ci->db->from('seeker_jobpreference_locations as location');
            $ci->db->join('seeker_jobpreference_locations_cities as city', 'city.location_id = location.id', 'left');
            $ci->db->where('location.jobpreference_id', $candidate['seekerPreferenceId']);
            $ci->db->group_by('city.city');
            $ci->db->order_by('city.city','asc');
            $preference_city = $ci->db->get()->result_array();

            ## round key for shortlisted section
            if (in_array('shortlisted', $fetchfor, true)) {
                // echo ('Please select a candidate'); die();
                $ci->db->select("
                    DISTINCT(round.id) as roundId,
                    round.round_name as roundName,
                    rtm.round_type as roundType,
                    round.round_mode as roundMode,
                    slot.id as slotId,
                    IFNULL(slot.notes,'') as slotFeedback,
                    slot.slot_name as slotName,
                    slot.meeting_link as slotMeetingLink,
                    slot.passcode as slotPasscode,
                    slot.test_link as slotTestLink,
                    slot.venue as slotVenue,
                    slot.notes as slotNotes,
                    concat(date,' ',start_time) as startDateTime,
                    concat(date,' ',end_time) as endDateTime,
                    slot.date as slotDate,
                    slot.start_time as slotStartTime,
                    slot.end_time as slotEndTime,
                    slot.duration as slotDuration,
                    slot.online_or_offline as slotOnlineOrOffline,
                    CASE WHEN slot.online_or_offline = 0 THEN 'Offline' WHEN slot.online_or_offline = 1 THEN 'Online' END as roundStatusMode,
                    slot.status as slotStatus,
                    CASE WHEN slot.status = 1 OR slot.status = 3 THEN '100' WHEN slot.status = 2  THEN '200' ELSE '300' END AS rankStatus,
                    CASE WHEN slot.status = 1  THEN 'Scheduled' WHEN slot.status = 2 THEN 'Cancelled' WHEN slot.status = 3 THEN 'Rescheduled' END as slotStatusMode,
                    UTC_TIMESTAMP() as utcTime,
                    CASE
                        WHEN concat(date,' ',start_time) >= UTC_TIMESTAMP() AND concat(date,' ',start_time) <= UTC_TIMESTAMP() AND slot.status = 1 THEN 'ONGOING'
                        WHEN concat(date,' ',start_time) > UTC_TIMESTAMP() AND slot.status = 1   THEN 'UPCOMING'
                        WHEN concat(date,' ',start_time) < UTC_TIMESTAMP() AND slot.status = 1  THEN 'COMPLETED'
                        WHEN concat(date,' ',start_time) > UTC_TIMESTAMP() AND slot.status = 3 THEN 'RESHEDULED' 
                        WHEN slot.status = 2 THEN 'CANCELLED'
                        ELSE ''
                    END as slotStatus1,
    
                ");
                $ci->db->from('shortlist_schedule_rounds as round');
                $ci->db->join('shortlist_schedule_round_slots as slot', 'round.id = slot.round_id');
                $ci->db->join('round_type_master as rtm', 'rtm.id = round.round_type_id');
                $ci->db->join('shortlist_schedule_slot_participant_map as ssspm', 'ssspm.slot_id = slot.id');
                $ci->db->where('ssspm.shortlist_id', $candidate['shortlistedId']);
                $ci->db->where('slot.status !=', NULL);

                // $ci->db->order_by('slot.status', 'ASC');
                //$ci->db->order_by('rankStatus', 'ASC');
                $ci->db->order_by('startDateTime', 'ASC');
                /* $ci->db->order_by("CASE
				    WHEN slotStatus = 'UPCOMING' THEN slotStartTime
				    ELSE '' END ASC");
                $ci->db->order_by("CASE
				    WHEN slotStatus = 'ONGOING' THEN slotStartTime
				    ELSE '' END DESC");
                $ci->db->order_by("CASE
				    WHEN slotStatus = 'COMPLETED' THEN slotStartTime
				    ELSE '' END DESC");
                $ci->db->order_by("CASE
				    WHEN slotStatus = 'RESHEDULED' THEN slotStartTime
				    ELSE '' END ASC");
                $ci->db->order_by("CASE
				    WHEN slotStatus = 'CANCELLED' THEN slotStartTime
				    ELSE '' END DESC"); */
                //$ci->db->order_by('slot.created_on', 'asc');
                $rounds = $ci->db->get()->result_array();

                $allRounds = array();

                if(!empty($rounds)){
                    $completedRound = array();
                    $ongoingRound = array();
                    $upcomingRound = array();
                    foreach($rounds as $roundData){
                        if($roundData['slotStatus1'] == 'COMPLETED'){
                            $completedRound[] = $roundData;
                        }else if($roundData['slotStatus1'] == 'ONGOING'){
                            $ongoingRound[] = $roundData;
                        }else if(in_array($roundData['slotStatus1'], array('UPCOMING', 'RESHEDULED', 'CANCELLED'))){
                            $upcomingRound[] = $roundData;
                        }
                    }

                    $allRounds = array_merge($completedRound, $ongoingRound, $upcomingRound);
                }

                $finalCandidateArr[$index]['rounds'] = $allRounds;
            } else {
                $finalCandidateArr[$index]['rounds'] = [];
            }
            // echo $seeker_preference[$index]['preferenceId'];die();

            $final_preference = array(
                'preferenceId' => !empty($seeker_preference['preferenceId']) ? $seeker_preference['preferenceId'] : 0,
                'employmentType' => !empty($seeker_preference['employmentType']) ? $seeker_preference['employmentType'] : '',
                'minimumSalary' => !empty($seeker_preference['minimumSalary']) ? $seeker_preference['minimumSalary'] : 0,
                'preferredLocations' => $preference_city,
                'skillsMatched' => get_skill_matched_count($jobId, $candidate['seekerIdReal']),
                'percentMatch' => !empty($seeker_preference['percentMatch']) ? $seeker_preference['percentMatch'] : 0,
                'interestLevel' => NULL
            );

            $finalCandidateArr[$index]['preference'] = $final_preference;
            #preference END<<<

            //latest comment object 
            $finalCandidateArr[$index]['latestComments'] = getEmployer_Latest_comment($jobId, $candidate['seekerIdReal']);

            $finalCandidateArr[$index]['contact'] = json_decode($finalCandidateArr[$index]['contact'], true);
            // $finalCandidateArr[$index]['latestComments'] = json_decode($finalCandidateArr[$index]['latestComments'],true);
            // $finalCandidateArr[$index]['preference'] = json_decode($finalCandidateArr[$index]['preference'],true);
            $finalCandidateArr[$index]['actedUponMessage'] = json_decode($finalCandidateArr[$index]['actedUponMessage'], true);

            if (sizeof($candidate_pref_arr) == 1) {
                $fetchforJob = (empty($jobObj)) ? $fetchfor : $jobObj;
                $finalCandidateArr[$index]['jobCardDetails'] = get_job_card_generic(array($jobId), $fetchforJob);
            }
        }
    } else {
        return array();
    }


    // echo "***************************** \n";

    // print_r(array_column($finalCandidateArr, 'seekerPreferenceId'));

    // die;
    return $finalCandidateArr;
}
## Candidate List generic helper for employer --Navneet 22-04-2022 END<<<

# Vijay - get Candidate Current CompanyName --04-05-2022 --Start
if (!function_exists('getCandidateCurrentCompanyName')) {
    function  getCandidateCurrentCompanyName($seeker_id)
    {
        $ci = &get_instance();
        $sql = "SELECT COUNT(seeker_id) as tot_seeker_id, institute_name, title FROM seeker_experience WHERE seeker_id = '" . $seeker_id . "' AND current_company = 'Yes' AND status = 1";

        $query = $ci->db->query($sql);

        $result = $query->row();

        if ($result->tot_seeker_id > 1) {
            $returnData = 'Working in multiple organisation';
        } else if ($result->tot_seeker_id == 1) {
            $returnData = $result->institute_name;
        } else {
            $returnData = null;
        }

        return $returnData;
    }
}
# Vijay - get Candidate Current CompanyName --04-05-2022 --Ends

if (!function_exists('getCandidateCurrentJobTitle')) {
    function  getCandidateCurrentJobTitle($seeker_id)
    {
        $ci = &get_instance();
        $sql = "SELECT COUNT(seeker_id) as tot_seeker_id, institute_name, title FROM seeker_experience WHERE seeker_id = '" . $seeker_id . "' AND current_company = 'Yes' AND status = 1";

        $query = $ci->db->query($sql);

        $result = $query->row();

        if ($result->tot_seeker_id > 1) {
            $returnData = 'Working in multiple organisation';
        } else if ($result->tot_seeker_id == 1) {
            $returnData = $result->title;
        } else {
            $returnData = null;
        }

        return $returnData;
    }
}

# Vijay - get Candidate Notice Period --04-05-2022 --Starts
if (!function_exists('getCandidateNoticePeriod')) {
    function  getCandidateNoticePeriod($seeker_id)
    {
        $ci = &get_instance();
        $sql = "SELECT COUNT(seeker_id) as tot_seeker_id, notice_period, MAX(notice_period) as max_notice_period FROM seeker_experience WHERE seeker_id = '" . $seeker_id . "' AND current_company = 'Yes' AND status = 1";

        $query = $ci->db->query($sql);

        $result = $query->row();

        if ($result->tot_seeker_id > 1) {
            $returnData = $result->max_notice_period;
        } else if ($result->tot_seeker_id == 1) {
            $returnData = $result->notice_period;
        } else {
            $returnData = null;
        }

        return $returnData;
    }
}
# Vijay - get Candidate Notice Period --04-05-2022 --Ends

# Vijay - get Education Specialization --05-05-2022 --Starts
if (!function_exists('getEducationSpecialization')) {
    function  getEducationSpecialization($seeker_id, $highestEducationId)
    {
        $ci = &get_instance();

        $sql = "SELECT IF(b.specialization_isother = 1, b.specialization_other, a.name) as educationSpecialization FROM education_specialisation AS a LEFT JOIN seeker_education AS b on b.specialization = a.id WHERE b.seeker_id = " . $seeker_id . " AND b.education_id = '" . $highestEducationId . "' AND a.status = 1";

        $query = $ci->db->query($sql);

        $result = $query->row();

        if (!empty($result)) {
            return $result->educationSpecialization;
        } else {
            return null;
        }
    }
}
# Vijay - get Education Specialization --05-05-2022 --End

if (!function_exists('get_countOf_vetting_and_approved_institutes')) {
    function get_countOf_vetting_and_approved_institutes($employer_id)
    {
        if ($employer_id == null) {
            return false;
        }

        $ci = &get_instance();

        # Vetting query
        $ci->db->select('COUNT(id)');
        $ci->db->from('institution_company_profiles');
        $ci->db->where('institution_company_id', $employer_id);
        $ci->db->where('under_vetting', 1);
        $ci->db->where('status', 1);
        $vettingQry = $ci->db->get_compiled_select();

        # Approved query
        $ci->db->select('COUNT(id)');
        $ci->db->from('institution_company_profiles');
        $ci->db->where('institution_company_id', $employer_id);
        $ci->db->where('under_vetting', 0);
        $ci->db->where('status', 1);
        $approvedQry = $ci->db->get_compiled_select();

        $ci->db->select('
    (' . $vettingQry . ') AS vetting,
    (' . $approvedQry . ') AS approved
    ');

        $result = $ci->db->get()->row_array();
        return $result;
    }
}

## Helper to get twofactorAuthentication or onefactorAuthentication
if (!function_exists('check_twofactorAuthentication')) {
    function check_twofactorAuthentication($employer_id)
    {
        $ci = &get_instance();
        $ci->db->select('security');
        $ci->db->from('institution_companies');
        $ci->db->where('id', $employer_id);
        $query = $ci->db->get()->row_array();
        // return $query;
        if ($query['security'] == 2) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('check_for_promotional_mail')) {
    function check_for_promotional_mail($employer_id)
    {
        $ci = &get_instance();
        $ci->db->select('receive_promotional_mail');
        $ci->db->from('institution_companies');
        $ci->db->where('id', $employer_id);
        $query = $ci->db->get()->row_array();
        // return $query;
        if ($query['receive_promotional_mail'] == 1) {
            return true;
        } else {
            return false;
        }
    }
}

//   (SELECT COUNT(id) FROM jobseeker_skill WHERE skill_id IN (SELECT skill_id FROM job_post_skill WHERE status = 1 AND job_id = ".$jobId.") AND status = 1)
//   AS skillsMatched,

##function to get count of  skill matched of candidates to a job post
if (!function_exists('get_skill_matched_count')) {
    function get_skill_matched_count($jobId, $seeker_id)
    {
        $ci = &get_instance();

        $ci->db->select('skill_id');
        $ci->db->from('job_post_skill');
        $ci->db->where('job_id', $jobId);
        $ci->db->where('status', 1);
        $query = $ci->db->get()->result_array();
        $jobPostSkillList = array_column($query, 'skill_id');
        // return $jobPostSkillList;die;
        $ci->db->flush_cache();

        $ci->db->select('id');
        $ci->db->from('jobseeker_skill');
        $ci->db->where('seeker_id', $seeker_id);
        $ci->db->where('skill_id IS NOT NULL', NULL, FALSE);

        $ci->db->where_in('id', !empty($jobPostSkillList) ? $jobPostSkillList : [-1]);
        $ci->db->where('status', 1);
        $final_skill_list = $ci->db->get()->result_array();

        return count($final_skill_list);
    }
}

if (!function_exists('getEmployer_Latest_comment')) {
    function getEmployer_Latest_comment($job_id, $seeker_id)
    {
        $ci = &get_instance();

        $ci->db->select('employer_id');
        $ci->db->from('job_post');
        $ci->db->where('id', $job_id);
        $query = $ci->db->get()->row_array();
        $employer_id = $query['employer_id'];

        $ci->db->flush_cache();

        $ci->db->select('comment AS comment,created_on AS dateTime');
        $ci->db->from('employer_employee_comments');
        $ci->db->where('employer_id', $employer_id);
        $ci->db->where('seeker_id', $seeker_id);
        $ci->db->where('job_id', $job_id);
        $ci->db->order_by('created_on DESC');


        $query = $ci->db->get()->result_array();
        return $query;
    }
}

if (!function_exists('getS3ImageCloudFrontWithFileKey')) {
    function getS3ImageCloudFrontWithFileKey($fileKey)
    {
        $ci = &get_instance();
        // require_once APPPATH . 'libraries/aws/aws-autoloader.php';
        $ci->config->load('s3', TRUE);
        $s3config = $ci->config->item('s3');
        $ci->s3 = new Aws\S3\S3Client($s3config['sharedConfig']);
        // $bucket = EMPLOYER_BUCKET;
        $bucket = EMPLOYEE_BUCKET;
        // $seekerFolder = get_seeker_info($seekerID)['seekerid'];
        // $key = $fileKey;

        if ($ci->s3->doesObjectExist($bucket, $fileKey)) {
            return CDN_ENDPOINT_FOR_EMPLOYEE . $fileKey;
        } else {
            return null;
        }
    }
}

if (!function_exists('getJobPreferenceDetail')) {
    function getJobPreferenceDetail($seekerID, $preferenceID)
    {
        $ci = &get_instance();
        $ci->db->select('id as preferenceID, job_category as jobCategory, institution_cat as institutionCat, institution_cat_any as institutionCatAny, institution_subcat as institutionSubCat, institution_subcat2 as institutionSubCat2, subject, subject_other as subjectOther, job_role as role, job_role_other as roleOther, title, is_customtitle as is_customTitle, customtitle as customTitle,is_actively_searching as  isActivelySearching, experience, min_salary as minSalary, employment_type as employmentType,system_title');
        $ci->db->where('seeker_id', $seekerID);
        if ($preferenceID) {
            $ci->db->where('id', $preferenceID);
        }

        $ci->db->where_in('status', [-1, -2, 1]); //updated by dharmesh
        return $ci->db->get('seeker_jobpreference')->row_array();
    }
}

//23-06-22 Dpz
if (!function_exists('generate_pagination_data')) {
    function generate_pagination_data($p, array $initialCandidateList)
    {
        $page_number = $p['page_index'];
        $per_page_element = $p['per_page_element'];
        $offset = ($page_number) * $per_page_element;
        $data_count = count($initialCandidateList);
        $total_pages = ceil($data_count / $per_page_element);

        try {
            if ($page_number > $total_pages  || $page_number < 0) {
                $candidateList = [];
                // throwException('RUNTIME_ERROR', 'Page number cannot exceed the total no. of available pages. Available_pages = ' . $total_pages . '. Found: ' . $page_number . ' in payload');
            } else {
                $candidateList = array_slice($initialCandidateList, $offset, $per_page_element);
            }
        } catch (\Throwable $e) {
            handleEx($e);
        }

        $page['count'] = $data_count;
        $page['currentPage'] = $page_number;
        $page['hasNextPage'] = $page_number < $total_pages ? true : false;
        $page['totalPages'] = $total_pages;
        $page['candidate_list'] = $candidateList;

        return $page;
    }
}

//12-07-22 Dpz

if (!function_exists('get_viewed_or_not_applied')) {
    function get_viewed_or_not_applied($job_id, $preference_id)
    {
        $ci = &get_instance();
        return $ci->db->select('is_viewed')->where('job_id', $job_id)->where('preference_id', $preference_id)->get('seeker_applied_job')->row_array()['is_viewed'];
    }
}


//12-07-22 Dpz

if (!function_exists('get_viewed_or_not_invited')) {
    function get_viewed_or_not_invited($job_id, $preference_id)
    {
        $ci = &get_instance();
        return $ci->db->select('is_viewed')->where('job_id', $job_id)->where('seeker_prefId', $preference_id)->get('employer_invited_employee')->row_array()['is_viewed'];
    }
}

if (!function_exists('get_recalculated_data')) {
    function get_recalculated_data($tableName, $jobId, $seekerId, $preferenceId, $resColumnName1, $reScolumnName2, $reqColumnName1, $reqColumnName2, $reqColumnName3)
    {
        $ci = &get_instance();
        // $query = $ci->db->select("IFNULL($resColumnName1,0) as recalculator_score,$reScolumnName2 as is_rejected, 
        $query = $ci->db->select("$resColumnName1 as recalculator_score,$reScolumnName2 as is_rejected, 
        (SELECT (system_title) FROM seeker_jobpreference WHERE id = " . $preferenceId . ") as systemTitle,is_perfectmatch as is_perfectmatch")
            ->from($tableName)
            ->where($reqColumnName1, $seekerId)
            ->where($reqColumnName2, $preferenceId)
            ->where($reqColumnName3, $jobId)
            ->get()->row_array();
        // echo $ci->db->last_query();die();
        return $query;
    }
}

// get employer group name
if (!function_exists('get_employer_group_name')) {
    function get_employer_group_name($id)
    {
        $ci = &get_instance();
        $ci->db->select('
            company_name
        ');
        $ci->db->from('institution_companies');
        $ci->db->where('id', $id);
        $ci->db->where("status", 1);
        $query = $ci->db->get();
        if ($query->num_rows())
            return $query->row_array();
        else
            return false;
    }
}

// Function to check if candidate is locked
if(!function_exists('is_candidate_unlocked')) {
  function is_candidate_unlocked($seeker_id, $job_id) {
    if(!$seeker_id || !$job_id) { return true; }        // Returning locked status in case of invalid parameters
    
    $ci =& get_instance();

    $result = $ci->db->select('id')
                      ->from('employee_contact_revealed')
                      ->where(['seeker_id' => $seeker_id, 'job_id' => $job_id, 'status' => 1])
                      ->get();

    return $result->num_rows() > 0 ? true : false;
  }
}

// Function to check if candidate is locked
if(!function_exists('get_email_template_details')) {
    function get_email_template_details($templateName) {
             
      $ci =& get_instance();
  
      $result = $ci->db->select('subject,body')
                        ->from('general_email_template')
                        ->where(['name' =>$templateName , 'status' => 1])
                        ->get();
  
      return $result->row_array();
    }
  }

  if(!function_exists('get_admin_details')) {
    function get_admin_details() {
             
      $ci =& get_instance();
  
      $result = $ci->db->select('*')
                        ->from('xx_general_settings')
                        ->where(['id' =>1])
                        ->get();
  
      return $result->row_array();
    }
  }

  if(!function_exists('is_domain_other_sector')) {
    function is_domain_other_sector($cat_id) {
             
      $ci =& get_instance();
  
      $result = $ci->db->select('name')
                        ->from('institute_categorizations')
                        ->where('id',$cat_id)
                        ->where('is_root',2)
                        ->where('status',1)
                        ->get();
        
        if($result->num_rows()){
            return $result->row_array();
        }else{
            return false;
        }
      
    }
  }









//live code
#Function to get EMPLOYER  system generated invoice url link using seeker_id as parameter
if(!function_exists('get_jie_systemGenerated_invoice_of_employer'))
{
     function get_jie_systemGenerated_invoice_of_employer($employerId,$purchasedId)
     {
        // require APPPATH . 'libraries/vendor/autoload.php';
        // require_once dirname(__FILE__)."/application/libraries/snappy/vendor/autoload.php";

        // use Knp\Snappy\Pdf;
        $ci = & get_instance();
        $ci->load->model('auth_model');
        
        $seekerinfo['admin_details'] = $ci->auth_model->getAdminDetails();
        $seekerinfo['employerBillingDetails'] = $ci->auth_model->getEmployerDetails($employerId);
        $seekerinfo['paymentDetails'] = $ci->auth_model->getEmployerPurchasedDetails($purchasedId);
        $purchaseAmount=$ci->auth_model->employerPackagePurchasedAmount($purchasedId);
        // print_r($purchaseAmount);die;
        $seekerinfo['purchaseDetails']=array(
            "amount"=>$purchaseAmount['price']-$purchaseAmount['price']*.18,
            "packageName"=> $purchaseAmount['package_name'] ,
            "subTotal"=>$purchaseAmount['price']-$purchaseAmount['price']*.18,
            "GST" => $purchaseAmount['price']*.18,
            "totalAmount"=> $purchaseAmount['price'],
            "purchaseCoinCount"=>$purchaseAmount['purchaseCoins']

        );
       
        $data['seekerinfo'] = $seekerinfo;
        $filelocation=$_SERVER['DOCUMENT_ROOT']."/uploads/employer/temp/";

        #icon in pdf in left top corner
        $url=base_url().'assets/img/invoicelogo.jpg';


        $handle = curl_init($url);
        
        curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

        // Get the HTML or whatever is linked in $url
        $response = curl_exec($handle);

        // Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
        /* Handle 404 here. */
            $url = "";  
        }

        curl_close($handle);


        /* Handle $response here. */
        $data['profileimg'] = $url;
        
        $html = $ci->load->view('auth/invoice_pdfgenhtml',$data,true);
        //exit($html);
        //$inputPath = $filelocation.'/Jobs_in_Education/'.'assets/img/companylogo.png';
        date_default_timezone_set('Asia/kolkata');
        $pdfname = date('FjYgias');
        //$snappy = new Knp\Snappy\Pdf();
        // $snappy = new Knp\Snappy\Pdf('C:/wkhtmltopdf/bin/wkhtmltopdf.exe'); //windows wkhtml2pdf
        $snappy = new Knp\Snappy\Pdf('/usr/local/bin/wkhtmltopdf'); //ubuntu wkhtml2pdf
        header('Content-Type: application/pdf');
        
        //  echo $filelocation.'\profile_pdf'.$pdfname.'.pdf';die;

        $snappy->generateFromHtml($html, $filelocation.'profile_pdf'.$pdfname.'.pdf');

        

        // $link= base_url()."uploads/employer/invoice/profile_pdf".$pdfname.".pdf ";
        $link= base_url()."uploads/employer/temp/profile_pdf".$pdfname.".pdf ";

        // $docUrl= $_SERVER['DOCUMENT_ROOT']."/uploads/employer/temp/profile_pdf".$pdfname.".pdf ";

        // $docUrl= "./uploads/employer/temp/profile_pdf".$pdfname.".pdf ";
        
        // header("Content-type:application/pdf");
        #tryin to save this file in our server
        $ci->load->library('s3');
        $pdfFileObject = 'invoices/invoice_'.hash('md5',current_datetime().rand(100,10000)).'.pdf';
        $ci->s3->savePdfObject(EMPLOYER_BUCKET, $link, $pdfFileObject);
        $link=CDN_ENDPOINT_FOR_EMPLOYER.$pdfFileObject;
        
        #putting an endpoint of generated pdf in employer_coin_purchase_history table to access it further
        $ci->db->where('id', $purchasedId);
        $ci->db->update('employer_coin_purchase_history', array('purchase_invoice'=>$pdfFileObject,'is_invoice_generated'=>1));
        return true;
     }
}

#*************** don't remove this commented code -->this is for local machine testing ***************#
// if(!function_exists('get_jie_systemGenerated_invoice_of_employer'))
// {
//      function get_jie_systemGenerated_invoice_of_employer($employerId,$purchasedId)
//      {
//         require APPPATH . 'libraries/vendor/autoload.php';
//         // require_once dirname(__FILE__)."/application/libraries/snappy/vendor/autoload.php";

//         // use Knp\Snappy\Pdf;
//         $ci = & get_instance();
//         $ci->load->model('auth_model');
        
//         $seekerinfo['admin_details'] = $ci->auth_model->getAdminDetails();
//         // print_r($seekerinfo);die;
//         $seekerinfo['employerBillingDetails'] = $ci->auth_model->getEmployerDetails($employerId);
//         // print_r($seekerinfo);die;

        
//         $seekerinfo['paymentDetails'] = $ci->auth_model->getEmployerPurchasedDetails($purchasedId);
//         // print_r($seekerinfo);die;
//         $purchaseAmount=$ci->auth_model->employerPackagePurchasedAmount($purchasedId);
//         $seekerinfo['purchaseDetails']=array(
//             "amount"=>$purchaseAmount['price']-$purchaseAmount['price']*.18,
//                         "packageName"=> $purchaseAmount['package_name'] ,
//                         "subTotal"=>$purchaseAmount['price']-$purchaseAmount['price']*.18,
//                         "GST" => $purchaseAmount['price']*.18,
//                         "totalAmount"=> $purchaseAmount['price'],
//                         "purchaseCoinCount"=>$purchaseAmount['purchaseCoins']

//         );
//         // print_r($seekerinfo['purchaseDetails']);die;
//         $data['seekerinfo'] = $seekerinfo;


        

//         $filedir=FCPATH."uploads/employer/temp";//testing

//         if( !is_dir($filedir)){
// 			mkdir($filedir, 0777);
// 		}
//         // echo $filedir;die;
//         // $filelocation=$_SERVER['DOCUMENT_ROOT']."/uploads/jobseeker/profile/";

//         // $filelocation=FCPATH."uploads\jobseeker\profile";//local tyesting
//         // echo $filelocation;die;




//         // $url = getEmployeeS3View_CDNUrl($seekerinfo['photo']);
//         // $filelocation=base_url().'assets/img/jiewatermark .png';
//         // echo $filelocation;die;

//         // $url = get_seeker_s3img(null);
//         // D:\XAMPP\JIE\htdocs\Jobs_in_Education\assets\img\invoicelogo.jpg
//         // $url = 'https://d1t7ou3mugtvf4.cloudfront.net/employer/vo0xbtrm/gr_lg_c126ffd5fc46309ec3d43223204876c3.png';
//         // echo $url;die;
//         // D:\XAMPP\JIE\htdocs\Jobs_in_Education\assets\img\invoicelogo.jpg
//         // $url='http://localhost/Jobs_in_Education/assets/img/invoicelogo.jpg';
//         $url=base_url().'assets/img/invoicelogo.jpg';
//         // echo $url;die;


//         $handle = curl_init($url);
        
//         curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

//         // Get the HTML or whatever is linked in $url
//         $response = curl_exec($handle);

//         // Check for 404 (file not found). */
//         $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
//         if($httpCode == 404) {
//         /* Handle 404 here. */
//         $url = "";  
//         }

//         curl_close($handle);

//         /* Handle $response here. */
//         $data['profileimg'] = $url;
//         //$data['profileimg'] = $filelocation.'/Jobs_in_Education/'.'assets/img/companylogo.png';
//         $html = $ci->load->view('auth/invoice_pdfgenhtml',$data,true);
//         //exit($html);
//         //$inputPath = $filelocation.'/Jobs_in_Education/'.'assets/img/companylogo.png';
//         // date_default_timezone_set('Asia/kolkata');
//         // $pdfname = date('FjYgias');
//         //$snappy = new Knp\Snappy\Pdf();
//         $snappy = new Knp\Snappy\Pdf('C:/wkhtmltopdf/bin/wkhtmltopdf.exe'); //windows wkhtml2pdf
//         // $snappy = new Knp\Snappy\Pdf('/usr/local/bin/wkhtmltopdf'); //ubuntu wkhtml2pdf
        
//         //  echo $filelocation.'\profile_pdf'.$pdfname.'.pdf';die;

//         // $snappy->generateFromHtml($html, $filelocation.'\profile_pdf'.$pdfname.'.pdf');
//         date_default_timezone_set('Asia/kolkata');

//         $pdfname = date('FjYgias');

//         $snappy->generateFromHtml($html, $filedir.'\profile_pdf'.$pdfname.'.pdf');



//         $docUrl= FCPATH."uploads/employer/temp/profile_pdf".$pdfname.".pdf ";
//         // if( !is_dir($docUrl)){
// 		// 	mkdir($docUrl, 0777);
// 		// }
//         $ci->load->library('s3');
		
//         // $blurFileKey = $newKeyArr[0].rand(100, 100000).'.'.end($newKeyArr);	    
//         $pdfFileObject = 'employer/invoice_'.hash('md5',current_datetime().rand(100,10000)).'.pdf';
//         $ci->s3->savePdfObject(EMPLOYER_BUCKET, $docUrl, $pdfFileObject);
//         $link=CDN_ENDPOINT_FOR_EMPLOYER.$pdfFileObject;
//         // echo $link;die;
//         // return $link;

//         $ci->db->where('id', $purchasedId);
//         $ci->db->update('employer_coin_purchase_history', array('purchase_invoice'=>$pdfFileObject));
//         return true;

//      }
// }

    #*************** don't remove this commented code -->this is for local machine testing ***************

// #Function to get seeker Jie system generated resume url link using seeker_id as parameter
// if(!function_exists('get_jie_systemGenerated_invoice_of_employer'))
// {
//      function get_jie_systemGenerated_invoice_of_employer($employerId,$purchasedId)
//      {
//         require APPPATH . 'libraries/vendor/autoload.php';
//         // require_once dirname(__FILE__)."/application/libraries/snappy/vendor/autoload.php";

//         // use Knp\Snappy\Pdf;
//         $ci = & get_instance();
//         $ci->load->model('auth_model');
        
//         $seekerinfo['admin_details'] = $ci->auth_model->getAdminDetails();
//         // print_r($seekerinfo);die;
//         $seekerinfo['employerBillingDetails'] = $ci->auth_model->getEmployerDetails($employerId);
//         // print_r($seekerinfo);die;

        
//         $seekerinfo['paymentDetails'] = $ci->auth_model->getEmployerPurchasedDetails($purchasedId);
//         // print_r($seekerinfo);die;
//         $purchaseAmount=$ci->auth_model->employerPackagePurchasedAmount($purchasedId);
//         $seekerinfo['purchaseDetails']=array(
//             "amount"=>$purchaseAmount['price']-$purchaseAmount['price']*.18,
//                         "packageName"=> $purchaseAmount['package_name'] ,
//                         "subTotal"=>$purchaseAmount['price']-$purchaseAmount['price']*.18,
//                         "GST" => $purchaseAmount['price']*.18,
//                         "totalAmount"=> $purchaseAmount['price'],
//                         "purchaseCoinCount"=>$purchaseAmount['purchaseCoins']

//         );
//         // print_r($seekerinfo['purchaseDetails']);die;
//         $data['seekerinfo'] = $seekerinfo;


        

//         $filedir=FCPATH."uploads\jobseeker\profile";//testing
//         // echo $filedir;die;
//         // $filelocation=$_SERVER['DOCUMENT_ROOT']."/uploads/jobseeker/profile/";

//         // $filelocation=FCPATH."uploads\jobseeker\profile";//local tyesting
//         // echo $filelocation;die;




//         // $url = getEmployeeS3View_CDNUrl($seekerinfo['photo']);
//         // $filelocation=base_url().'assets/img/jiewatermark .png';
//         // echo $filelocation;die;

//         // $url = get_seeker_s3img(null);
//         // D:\XAMPP\JIE\htdocs\Jobs_in_Education\assets\img\invoicelogo.jpg
//         // $url = 'https://d1t7ou3mugtvf4.cloudfront.net/employer/vo0xbtrm/gr_lg_c126ffd5fc46309ec3d43223204876c3.png';
//         // echo $url;die;
//         // D:\XAMPP\JIE\htdocs\Jobs_in_Education\assets\img\invoicelogo.jpg
//         // $url='http://localhost/Jobs_in_Education/assets/img/invoicelogo.jpg';
//         $url=base_url().'assets/img/invoicelogo.jpg';
//         // echo $url;die;


//         $handle = curl_init($url);
        
//         curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

//         // Get the HTML or whatever is linked in $url
//         $response = curl_exec($handle);

//         // Check for 404 (file not found). */
//         $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
//         if($httpCode == 404) {
//         /* Handle 404 here. */
//         $url = "";  
//         }

//         curl_close($handle);

//         /* Handle $response here. */
//         $data['profileimg'] = $url;
//         //$data['profileimg'] = $filelocation.'/Jobs_in_Education/'.'assets/img/companylogo.png';
//         $html = $ci->load->view('auth/invoice_pdfgenhtml',$data,true);
//         //exit($html);
//         //$inputPath = $filelocation.'/Jobs_in_Education/'.'assets/img/companylogo.png';
//         // date_default_timezone_set('Asia/kolkata');
//         // $pdfname = date('FjYgias');
//         //$snappy = new Knp\Snappy\Pdf();
//         $snappy = new Knp\Snappy\Pdf('C:/wkhtmltopdf/bin/wkhtmltopdf.exe'); //windows wkhtml2pdf
//         // $snappy = new Knp\Snappy\Pdf('/usr/local/bin/wkhtmltopdf'); //ubuntu wkhtml2pdf
        
//         //  echo $filelocation.'\profile_pdf'.$pdfname.'.pdf';die;

//         // $snappy->generateFromHtml($html, $filelocation.'\profile_pdf'.$pdfname.'.pdf');
//         date_default_timezone_set('Asia/kolkata');

//         $pdfname = date('FjYgias');

//         $snappy->generateFromHtml($html, $filedir.'\profile_pdf'.$pdfname.'.pdf');



//         //add watermark to the pdf 

//         $filename1 = '\profile_pdf'.$pdfname.'.pdf';
//         // Source file and watermark config 
//         $file1 = $filedir.$filename1;

//         // echo $file;die;

//         // $text_image='D:\XAMPP\JIE\htdocs\Jobs_in_Education\assets\img'.'\jiewatermark.png';
//     //  D:\XAMPP\JIE\htdocs\Jobs_in_Education\assets\img
//         // $text_image = $_SERVER['DOCUMENT_ROOT'].'/assets/img/jiewatermark.png';
//         // echo $text_image;die;
//         // Set source PDF file 


//         $pdf = new setasign\Fpdi\Fpdi(); 
//         if(file_exists($file1)) {
//             $pagecount = $pdf->setSourceFile($file1);
//         }
//         else {
//             die('Source PDF not found!');
//         } 
          
//          // Add watermark image to PDF pages 
//         // for($i=1;$i<=$pagecount;$i++) {
//         //     $tpl = $pdf->importPage($i);
//         //     $size = $pdf->getTemplateSize($tpl);
//         //     $pdf->addPage();
//         //     $pdf->useTemplate($tpl, 1, 1, $size['width'], $size['height'], TRUE);
                
//         //     //Put the watermark
//         //     $xxx_final = (50);
//         //     $yyy_final = (50);
//         //     $pdf->Image($text_image, $xxx_final, $yyy_final, 0, 0, 'png');
//         // }
          
//         //  // Output PDF with watermark
//         // $pdf->Output('F',$file1);



//         // $link = base_url()."uploads/jobseeker/profile/".$filename;

//         $link= base_url()."uploads/jobseeker/profile/profile_pdf".$pdfname.".pdf ";

        


//         // print_r($link );die;
//         return $link;
//      }
// }


/**
 * checking jobid required any skills or not 
 */
if (!function_exists('jobRequiredAnySkills')) {
    function jobRequiredAnySkills($job_id)
    {
        $jobIds = array_column($job_id, 'job_id');
        // print_r($jobIds); die;
      $ci = &get_instance();
      $query = $ci->db->select('COUNT(id) as c')
        ->from('job_post_skill')
        ->where_in('job_id', $jobIds)
        ->where('skill_id !=', NULL)
        ->where('status', 1)
        ->get();
  
      $sql =$query->row();
    //    echo $ci->db->last_query(); die();
     return $sql->c;
    }
  }


  if (!function_exists('checkJobHaveAnySkill')) {
    function checkJobHaveAnySkill($jobIds)
    {
      $ci = &get_instance();
      $query = $ci->db->select('COUNT(id) as c')
        ->from('job_post_skill')
        ->where_in('job_id', $jobIds)
        ->where('skill_id !=', NULL)
        ->where('status', 1)
        ->get();
  
      $sql =$query->row();
    //    echo $ci->db->last_query(); die();
     return $sql->c;
    }
  }

  /**
   * CandidateHaveAnySkills or not
   */
if (!function_exists('CandidateHaveAnySkills')){
    function CandidateHaveAnySkills($seekerId) {
        $ci = &get_instance();
        $query = $ci->db->select('COUNT(id) as c')
        ->where_in('seeker_id', $seekerId)
        ->where('skill_id !=', NULL)
        ->where('status', 1)
        ->get('jobseeker_skill');
  
      $sql =$query->row();
     return $sql->c;
    }
  }

  /**
   * getSeekerSkill with the help of seekerid
   */
  if (!function_exists('getSeekerSkills')){
    function getSeekerSkills($seekerID) {
        $ci = &get_instance();
    if(!$seekerID) return [];
    $ci->db->select('skill_id');
    $ci->db->from('jobseeker_skill');
    $ci->db->where(['status' => 1, 'seeker_id' => $seekerID, 'skill_id !=' => null]);
    return array_column($ci->db->get()->result_array(), 'skill_id');
    }
  }

  #remove candidates from employer side or make the candidates fresh
  if (!function_exists('flushCandidatesActivity')){
    function flushCandidatesActivity($seekerID,$preferenceID,$jobId) {
        $ci = &get_instance();
        
        $return_type=true;

        #applying transaction and rollback-->
        $ci->db->trans_begin();

        #remove candidates from unlocked(employee_contact_revealed) table
        $ci->db->where("seeker_id", $seekerID);
        $ci->db->where("job_id", $jobId);
        $ci->db->update("employee_contact_revealed",array("status" => null));

        #remove candidates from seeker_applied_job table
        $ci->db->where("seeker_id", $seekerID);
        $ci->db->where("preference_id",$preferenceID);
        $ci->db->where("job_id", $jobId);
        $ci->db->update("seeker_applied_job",array("status" => null));

        #remove candidates from employer_invited_employee table
        $ci->db->where("seeker_id", $seekerID);
        $ci->db->where("seeker_prefId",$preferenceID);
        $ci->db->where("job_id", $jobId);
        $ci->db->update("employer_invited_employee",array("status" => null));

        #enlist shortlisted candidates
        $ci->db->select("id");
        $ci->db->from("employee_shortlisted");
        $ci->db->where("job_id", $jobId);
        $ci->db->where("seeker_id",$seekerID);
        $ci->db->where("seeker_prefId",$preferenceID);
        $ci->db->where("status",1);
        $shortlistedData=$ci->db->get()->result_array();
        $shorlistedIds=array_column($shortlistedData,'id');

        #remove candidates from employee_shortlisted table
        $ci->db->where("seeker_id", $seekerID);
        $ci->db->where("job_id", $jobId);
        $ci->db->where("seeker_prefId",$preferenceID);
        $ci->db->update("employee_shortlisted",array("status" => null));

        #remove candidates from onhold section
        if(!empty($shorlistedIds)) {
            // $ci->db->where("job_id", $jobId);
            $ci->db->where_in("shortlist_id", $shorlistedIds);
            $ci->db->update("employee_holded",array("status" => null));
        }
        
        #remove candidates from employer_saved_employee table
        $ci->db->where("seeker_id", $seekerID);
        $ci->db->where("job_id", $jobId);
        $ci->db->where("seeker_prefId",$preferenceID);
        $ci->db->update("employer_saved_employee",array("status" => null));

        if ($ci->db->trans_status() === FALSE){
            $ci->db->trans_rollback();
            $return_type=false;
        }else{
            $ci->db->trans_commit();
        }#transaction and rollback end<--

        return $return_type;
       
    }
  }

  if (!function_exists('is_employer_email_valid')){
    function is_employer_email_valid($employerId) {
        $ci = &get_instance();

        $ci->db->select("is_email_manual,is_email_changed");
        $ci->db->from("institution_companies");
        $ci->db->where("id",$employerId);
        $ci->db->where("status",1);
        $result=$ci->db->get()->row_array();

        if($result['is_email_manual']==1 && $result['is_email_changed']==0){
            return false;
        }else{
            return true;
        }

    }
  }

  if (!function_exists('is_employer_mobile_valid')){
    function is_employer_mobile_valid($employerId) {
        $ci = &get_instance();

        $ci->db->select("is_mobile_manual,is_mobile_changed");
        $ci->db->from("institution_companies");
        $ci->db->where("id",$employerId);
        $ci->db->where("status",1);
        $result=$ci->db->get()->row_array();

        if($result['is_mobile_manual']==1 && $result['is_mobile_changed']==0){
            return false;
        }else{
            return true;
        }

    }
  }

  if (!function_exists('get_instituion_name_id')){
    function get_instituion_name_id($instituteId) {
        $ci = &get_instance();

        $ci->db->select("institution_name");
        $ci->db->from("institution_company_profiles");
        $ci->db->where("id",$instituteId);
        $ci->db->where("status",1);
        $result=$ci->db->get()->row_array();

        return $result;
    }
  }

  if (!function_exists('getSlotDetailsForMailTrigger')){
    function getSlotDetailsForMailTrigger($slotId) {
        $ci = &get_instance();

        $ci->db->select("*");
        $ci->db->from("shortlist_schedule_round_slots");
        $ci->db->where("id",$slotId);
         $ci->db->where_in("status",[1,3]);
        $result=$ci->db->get()->row_array();

        return $result;
    }
  }

  if (!function_exists('getRoundTypeFromMaster')){
    function getRoundTypeFromMaster($roundId) {
        $ci = &get_instance();

        $ci->db->select("round_type_id");
        $ci->db->from("shortlist_schedule_rounds");
        $ci->db->where("id",$roundId);
        $ci->db->where("status",1);
        return $ci->db->get()->row_array();
    }
  }

  if (!function_exists('get_user_slot_details_by_id')){
    function get_user_slot_details_by_id($slotId) {
        $ci = &get_instance();

        $ci->db->select("*");
        $ci->db->from("shortlist_schedule_round_slots");
        $ci->db->where("id",$slotId);
        $ci->db->where_in("status",[1,3]);
        return $ci->db->get()->row_array();
    }
  }

  if (!function_exists('get_panelist_details_by_id')){
    function get_panelist_details_by_id($panelistId) {
        $ci = &get_instance();

        $ci->db->select("*");
        $ci->db->from("employer_panelist");
        $ci->db->where("id",$panelistId);
        $ci->db->where("status",1);
        return $ci->db->get()->row_array();
    }
  }
  if (!function_exists('get_user_round_details_by_id')){
    function get_user_round_details_by_id($roundId) {
        $ci = &get_instance();

        $ci->db->select("*");
        $ci->db->from("shortlist_schedule_rounds");
        $ci->db->where("id",$roundId);
        $ci->db->where("status",1);
        return $ci->db->get()->row_array();
    }
  }

  if (!function_exists('get_institution_details_by_jobId')){
    function get_institution_details_by_jobId($jobId) {
        $ci = &get_instance();

        $ci->db->select("
                employer_id,
                CASE
                    WHEN for_entire_group=1 THEN function_get_CompanyName(employer_id) 
                    WHEN for_entire_group=0 THEN function_get_institutionNameByinstitionId(institute_profile_id)
                END AS InstituteName,
                (CASE 
                    WHEN is_customtitle=1 THEN customtitle
                    ELSE title
                END) AS title
        ");
        $ci->db->from("job_post");
        $ci->db->where("id",$jobId);
        $ci->db->where("status!=",null);
        return $ci->db->get()->row_array();
    }
  }

  if (!function_exists('get_shorlisted_candidate_details')){
    function get_shorlisted_candidate_details($shorlistedId) {
        $ci = &get_instance();

        $ci->db->select("
            shorlisted.id,
            shorlisted.employer_id,
            CASE 
                WHEN shorlisted.institution_id=null THEN function_get_CompanyName(shorlisted.employer_id)
                ELSE function_get_institutionNameByinstitionId(shorlisted.institution_id)
            END AS InstituteName,
            shorlisted.institution_id,
            (
                select first_name from jobseeker where id=shorlisted.seeker_id and status=1
            ) AS seekerName,
            (
                select email from jobseeker where id=shorlisted.seeker_id and status=1
            ) AS email,
            shorlisted.seeker_id,
            shorlisted.job_id,
            (
                select (CASE 
                            WHEN is_customtitle=1 THEN customtitle
                            ELSE title  
                        END ) AS title
                from job_post where id=shorlisted.job_id and status is not null
            
            ) AS title
        ","FALSE");
        $ci->db->from("employee_shortlisted as shorlisted");
        $ci->db->where("shorlisted.id",$shorlistedId);
        $ci->db->where("shorlisted.status",1);
        return $ci->db->get()->row_array();
    }
  }

  