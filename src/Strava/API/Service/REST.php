<?php

namespace Strava\API\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Strava REST Service
 *
 * @author Bas van Dorst
 * @package StravaPHP
 */
class REST implements ServiceInterface
{
    /**
     * REST adapter
     */
    protected Client $adapter;

    /**
     * Application token
     */
    protected string $token;

    /**
     * Specifies the verbosity of the HTTP response.
     * 0 = basic, just body
     * 1 = enhanced, [body, headers, status]
     */
    protected int $responseVerbosity;

    /**
     * Initiate this REST service with the application token, a instance
     * of the REST adapter (Guzzle) and a level of verbosity for the response.
     *
     * @param string|AccessTokenInterface $token
     * @param Client $adapter
     * @param int $responseVerbosity
     */
    public function __construct($token, Client $adapter, int $responseVerbosity = 0)
    {
        if (is_object($token) && method_exists($token, 'getToken')) {
            $token = $token->getToken();
        }
        $this->token = $token;
        $this->adapter = $adapter;
        $this->responseVerbosity = $responseVerbosity;
    }

    protected function getToken()
    {
        return $this->token;
    }

    /**
     * Get a request result.
     * Returns an array with a response body or and error code => reason.
     * @param ResponseInterface|string $response
     * @return array|string
     */
    protected function getResult($response)
    {
        // Workaround for export methods getRouteAsGPX, getRouteAsTCX:
        if (is_string($response)) {
            return $response;
        }

        $status = $response->getStatusCode();

        $expandedResponse = [];

        $expandedResponse['headers'] = $response->getHeaders();
        $expandedResponse['body'] = json_decode($response->getBody(), true);
        $expandedResponse['success'] = $status === 200 || $status === 201;
        $expandedResponse['status'] = $status;

        return $expandedResponse;
    }

    /**
     * Get an API request response and handle possible exceptions.
     *
     * @param string $method
     * @param string $path
     * @param array $parameters
     *
     * @return array|mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException|Exception
     */
    protected function getResponse(string $method, string $path, array $parameters)
    {
        try {
            $response = $this->adapter->request($method, $path, $parameters);
            $result = $this->getResult($response);

            if ($this->responseVerbosity === 0 && !is_string($result)) {
                return $result["body"];
            }

            return $result;
        } catch (\Exception $e) {
            throw new Exception('[SERVICE] ' . $e->getMessage());
        }
    }

    public function getAthlete(int $id = null)
    {
        $path = 'athlete';
        if (isset($id)) {
            $path = 'athletes/' . $id;
        }
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteStats(int $id)
    {
        $path = 'athletes/' . $id . '/stats';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteRoutes(int $id, string $type = null, int $after = null, int $page = null, int $per_page = null)
    {
        $path = 'athletes/' . $id . '/routes';
        $parameters['query'] = [
            'type' => $type,
            'after' => $after,
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteClubs()
    {
        $path = 'athlete/clubs';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteActivities(string $before = null, string $after = null, int $page = null, int $per_page = null)
    {
        $path = 'athlete/activities';
        $parameters['query'] = [
            'before' => $before,
            'after' => $after,
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteFriends(int $id = null, int $page = null, int $per_page = null)
    {
        $path = 'athlete/friends';
        if (isset($id)) {
            $path = 'athletes/' . $id . '/friends';
        }
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteFollowers(int $id = null, int $page = null, int $per_page = null)
    {
        $path = 'athlete/followers';
        if (isset($id)) {
            $path = 'athletes/' . $id . '/followers';
        }
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteBothFollowing(int $id, int $page = null, int $per_page = null)
    {
        $path = 'athletes/' . $id . '/both-following';
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteKom(int $id, int $page = null, int $per_page = null)
    {
        $path = 'athletes/' . $id . '/koms';
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteZones()
    {
        $path = 'athlete/zones';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getAthleteStarredSegments(int $id = null, int $page = null, int $per_page = null)
    {
        $path = 'segments/starred';
        if (isset($id)) {
            $path = 'athletes/' . $id . '/segments/starred';
            // ...wrong in Strava documentation
        }
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function updateAthlete(string $city, string $state, string $country, string $sex, float $weight)
    {
        $path = 'athlete';
        $parameters['query'] = [
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'sex' => $sex,
            'weight' => $weight,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('PUT', $path, $parameters);
    }

    public function getActivity(int $id, bool $include_all_efforts = null)
    {
        $path = 'activities/' . $id;
        $parameters['query'] = [
            'include_all_efforts' => $include_all_efforts,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getActivityComments(int $id, bool $markdown = null, int $page = null, int $per_page = null)
    {
        $path = 'activities/' . $id . '/comments';
        $parameters['query'] = [
            'markdown' => $markdown,
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getActivityKudos(int $id, int $page = null, int $per_page = null)
    {
        $path = 'activities/' . $id . '/kudos';
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getActivityPhotos(int $id, int $size = 2048, string $photo_sources = 'true')
    {
        $path = 'activities/' . $id . '/photos';
        $parameters['query'] = [
            'size' => $size,
            'photo_sources' => $photo_sources,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getActivityZones(int $id)
    {
        $path = 'activities/' . $id . '/zones';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getActivityLaps(int $id)
    {
        $path = 'activities/' . $id . '/laps';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getActivityUploadStatus(int $id)
    {
        $path = 'uploads/' . $id;
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function createActivity(string $name, string $type, string $start_date_local, int $elapsed_time, string $description = null, float $distance = null, int $private = null, int $trainer = null)
    {
        $path = 'activities';
        $parameters['query'] = [
            'name' => $name,
            'type' => $type,
            'start_date_local' => $start_date_local,
            'elapsed_time' => $elapsed_time,
            'description' => $description,
            'distance' => $distance,
            'private' => $private,
            'trainer' => $trainer,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('POST', $path, $parameters);
    }

    public function uploadActivity(string $file, string $activity_type = null, string $name = null, string $description = null, int $private = null, int $trainer = null, int $commute = null, string $data_type = null, string $external_id = null)
    {
        $path = 'uploads';
        $parameters['query'] = [
            'activity_type' => $activity_type,
            'name' => $name,
            'description' => $description,
            'private' => $private,
            'trainer' => $trainer,
            'commute' => $commute,
            'data_type' => $data_type,
            'external_id' => $external_id,
            'file' => curl_file_create($file),
            'file_hack' => '@' . ltrim($file, '@'),
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('POST', $path, $parameters);
    }

    public function updateActivity(int $id, string $name = null, string $type = null, bool $private = false, bool $commute = false, bool $trainer = false, string $gear_id = null, string $description = null)
    {
        $path = 'activities/' . $id;
        $parameters['query'] = [
            'name' => $name,
            'type' => $type,
            'private' => $private,
            'commute' => $commute,
            'trainer' => $trainer,
            'gear_id' => $gear_id,
            'description' => $description,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('PUT', $path, $parameters);
    }

    public function deleteActivity(int $id)
    {
        $path = 'activities/' . $id;
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('DELETE', $path, $parameters);
    }

    public function getGear(int $id)
    {
        $path = 'gear/' . $id;
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getClub(int $id)
    {
        $path = 'clubs/' . $id;
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getClubMembers(int $id, int $page = null, int $per_page = null)
    {
        $path = 'clubs/' . $id . '/members';
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getClubActivities(int $id, int $page = null, int $per_page = null)
    {
        $path = 'clubs/' . $id . '/activities';
        $parameters['query'] = [
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getClubAnnouncements(int $id)
    {
        $path = 'clubs/' . $id . '/announcements';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getClubGroupEvents(int $id)
    {
        $path = 'clubs/' . $id . '/group_events';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function joinClub(int $id)
    {
        $path = 'clubs/' . $id . '/join';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('POST', $path, $parameters);
    }

    public function leaveClub(int $id)
    {
        $path = 'clubs/' . $id . '/leave';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('POST', $path, $parameters);
    }

    public function getRoute(int $id)
    {
        $path = 'routes/' . $id;
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getRouteAsGPX(int $id)
    {
        $path = 'routes/' . $id . '/export_gpx';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getRouteAsTCX(int $id)
    {
        $path = 'routes/' . $id . '/export_tcx';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getSegment(int $id)
    {
        $path = 'segments/' . $id;
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getSegmentLeaderboard(int $id, string $gender = null, string $age_group = null, $weight_class = null, $following = null, $club_id = null, $date_range = null, $context_entries = null, $page = null, $per_page = null)
    {
        $path = 'segments/' . $id . '/leaderboard';
        $parameters['query'] = [
            'gender' => $gender,
            'age_group' => $age_group,
            'weight_class' => $weight_class,
            'following' => $following,
            'club_id' => $club_id,
            'date_range' => $date_range,
            'context_entries' => $context_entries,
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getSegmentExplorer(string $bounds, string $activity_type = 'riding', int $min_cat = null, int $max_cat = null)
    {
        $path = 'segments/explore';
        $parameters['query'] = [
            'bounds' => $bounds,
            'activity_type' => $activity_type,
            'min_cat' => $min_cat,
            'max_cat' => $max_cat,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getSegmentEffort(int $id, int $athlete_id = null, string $start_date_local = null, string $end_date_local = null, int $page = null, int $per_page = null)
    {
        $path = 'segments/' . $id . '/all_efforts';
        $parameters['query'] = [
            'athlete_id' => $athlete_id,
            'start_date_local' => $start_date_local,
            'end_date_local' => $end_date_local,
            'page' => $page,
            'per_page' => $per_page,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getStreamsActivity(int $id, string $types, $resolution = null, string $series_type = 'distance')
    {
        $path = 'activities/' . $id . '/streams/' . $types;
        $parameters['query'] = [
            'resolution' => $resolution,
            'series_type' => $series_type,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getStreamsEffort(int $id, string $types, $resolution = null, string $series_type = 'distance')
    {
        $path = 'segment_efforts/' . $id . '/streams/' . $types;
        $parameters['query'] = [
            'resolution' => $resolution,
            'series_type' => $series_type,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getStreamsSegment(int $id, string $types, $resolution = null, string $series_type = 'distance')
    {
        $path = 'segments/' . $id . '/streams/' . $types;
        $parameters['query'] = [
            'resolution' => $resolution,
            'series_type' => $series_type,
            'access_token' => $this->getToken(),
        ];

        return $this->getResponse('GET', $path, $parameters);
    }

    public function getStreamsRoute(int $id)
    {
        $path = 'routes/' . $id . '/streams';
        $parameters['query'] = ['access_token' => $this->getToken()];

        return $this->getResponse('GET', $path, $parameters);
    }
}
