<?php

namespace Blastengine;

class Job extends Base
{
	private int $_job_id;
	public ?int $percentage;
	public ?string $status;
	public ?int $success_count;
	public ?int $failed_count;
	public ?int $total_count;
	public ?string $error_file_url;
	public bool $error = false;
	public ?string $error_report;

	public function __construct(int $id) {
		$this->_job_id = $id;
		parent::__construct();
	}

	public function get(): bool {
		if (!isset($this->_job_id)) {
			throw new Exception("No job id found.");
		}
		$path = sprintf("/deliveries/-/emails/import/%s", $this->_job_id);
		$res = $this->_apiClient->get($path);
		if (isset($res["percentage"])) {
			$this->percentage = $res["percentage"];
		}
		if (isset($res["status"])) {
			$this->status = $res["status"];
		}
		if (isset($res["success_count"])) {
			$this->success_count = $res["success_count"];
		}
		if (isset($res["failed_count"])) {
			$this->failed_count = $res["failed_count"];
		}
		if (isset($res["total_count"])) {
			$this->total_count = $res["total_count"];
		}
		if (isset($res["error_file_url"])) {
			$this->error_file_url = $res["error_file_url"];
			$this->error = true;
		}
		return true;
	}

	public function finished(): bool {
		$this->get();
		return $this->percentage == 100;
	}

	public function error_report(): string {
		$path = sprintf("/deliveries/-/emails/import/%s/errorinfo/download", $this->_job_id);
		$res = $this->_apiClient->get($path, true);
		$this->error_report = $res["raw"];
		return $this->error_report;
	}
}
