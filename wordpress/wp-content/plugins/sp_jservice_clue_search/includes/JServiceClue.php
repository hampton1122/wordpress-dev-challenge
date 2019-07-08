<?php

class JServiceClue
{
    /** @var string jservice api endpoint for clues */
    const JSERVICE_API_CLUES = 'http://jservice.io/api/clues';

    /** @var string JSON clues we have pulled from the api */
    private $clues;

    /** @var int value of the clue in dollars, like songs for $200 would be $value = 200 */
    private $value;

    /** @var int the category to pull clues for, see http://jservice.io/ */
    private $category;

    /** @var DateTime filter out clues aired earlier than this date */
    private $minDate;

    /** @var DateTime filter out clues aired more recent than this date */
    private $maxDate;

    /** @var int make this clue id your first id, then pull as many as you can (for pagination) */
    private $offset;

    /** @var bool determine if there are any search filters */
    private $getAllClues = false;

    /** @var array of errors validating options or calling the api */
    private $errors = [];

    public function __construct(array $args)
    {
        $this->setOpts($args);
        $this->setClues();
    }

    private function setOpts(array $args)
    {
        if (empty($args)) {
            $this->getAllClues = true;
        }

        if (!empty($args['value'])) {
            $this->setValue($args['value']);
        }

        if (!empty($args['category'])) {
            $this->setCategory($args['category']);
        }

        if (!empty($args['minDate'])) {
            $this->setMinDate($args['minDate']);
        }

        if (!empty($args['maxDate'])) {
            $this->setMaxDate($args['maxDate']);
        }

        if (!empty($args['offset'])) {
            $this->setOffset($args['offset']);
        }
    }

    public function getClues()
    {
        return $this->clues;
    }

    private function setClues()
    {
        if ($this->getAllClues) {
            $clues = $this->pullAllClues();
        } else {
            $url = self::JSERVICE_API_CLUES . '?' . http_build_query($this->getArgs());
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $clues = curl_exec($ch);
        }

        $this->clues = $clues;
    }

    private function getArgs()
    {
        $args = [];

        if (isset($this->value)) {
            $args['value'] = $this->value;
        }

        if (isset($this->category)) {
            $args['category'] = $this->category;
        }

        if (isset($this->minDate)) {
            $args['minDate'] = $this->minDate;
        }

        if (isset($this->maxDate)) {
            $args['maxDate'] = $this->maxDate;
        }

        if (isset($this->offset)) {
            $args['offset'] = $this->offset;
        }

        return $args;
    }

    private function pullAllClues()
    {
        $continue = false;
        $resultsTotal = 0;

        do {
            $multiCurl = [];
            $clues = [];
            $mch = curl_multi_init();

            for ($i = 0; $i < 10; $i++) {
                $url = self::JSERVICE_API_CLUES . '?offset=' . $resultsTotal;
                $multiCurl[$i] = curl_init();
                curl_setopt($multiCurl[$i], CURLOPT_URL, $url);
                curl_setopt($multiCurl[$i], CURLOPT_HEADER, 0);
                curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, 1);
                curl_multi_add_handle($mch, $multiCurl[$i]);
                $resultsTotal += 100;
            }

            $running = false;

            do {
                curl_multi_exec($mch, $running);
            } while ($running);

            foreach ($multiCurl as $k => $ch) {
                $curlData = curl_multi_getcontent($ch);

                if (empty($curlData)) {
                    $continue = false;
                } else {
                    $clues[$k] = $curlData;
                    $continue = true;
                }

                curl_multi_remove_handle($mch, $ch);
            }

            curl_multi_close($mch);
        } while ($continue);

        return $clues;
    }

    private function setValue($value)
    {
        $errorMsg = 'Value was not valid, ';
        $valid = true;

        if (!is_numeric($value)) {
            $valid = false;
            $this->addError($errorMsg . 'because it was not numeric.');
        }

        //might be able to get all acceptable values, even if it's not enforced by jservice
        if ($value <= 0) {
            $valid = false;
            $this->addError($errorMsg . 'because it was less than or equal to 0.');
        }

        if (true === $valid) {
            $this->value = $value;
        }
    }

    private function setCategory($category)
    {
        $errorMsg = 'Category was not valid, ';
        $valid = true;

        if (!is_numeric($category)) {
            $valid = false;
            $this->addError($errorMsg . 'because it was not numeric.');
        }

        if (true === $valid) {
            $this->category = $category;
        }
    }

    private function setMinDate($minDate)
    {
        $errorMsg = 'minDate was not valid, ';
        $valid = true;

        if (false === DateTime::createFromFormat('Y-m-d', $minDate)) {
            $valid = false;
            $this->addError($errorMsg . 'because it was not a valid date.');
        }

        if (true === $valid) {
            $this->minDate = $minDate;
        }
    }

    private function setMaxDate($maxDate)
    {
        $errorMsg = 'maxDate was not valid, ';
        $valid = true;

        if (false === DateTime::createFromFormat('Y-m-d', $maxDate)) {
            $valid = false;
            $this->addError($errorMsg . 'because it was not a valid date.');
        }

        if (true === $valid) {
            $this->maxDate = $maxDate;
        }
    }

    private function setOffset($offset)
    {
        $errorMsg = 'Offset was not valid, ';
        $valid = true;

        if (!is_numeric($offset)) {
            $valid = false;
            $this->addError($errorMsg . 'because it was not numeric.');
        }

        if ($offset < 0) {
            $valid = false;
            $this->addError($errorMsg . 'because it was negative.');
        }

        if (true === $valid) {
            $this->offset = $offset;
        }
    }

    private function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}