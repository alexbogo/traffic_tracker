<?php

namespace App\Entity;

use App\Repository\VisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitRepository::class)]
#[ORM\Table(name: 'visits')]
#[ORM\Index(name: 'idx_page_visited', columns: ['page_id', 'visited_at'])]
#[ORM\Index(name: 'idx_fingerprint', columns: ['visitor_fingerprint'])]
#[ORM\Index(name: 'idx_visited_at', columns: ['visited_at'])]
#[ORM\Index(name: 'idx_country', columns: ['ip_country_code'])]
#[ORM\Index(name: 'idx_bot', columns: ['is_bot'])]
#[ORM\Index(name: 'idx_session', columns: ['session_id'])]
#[ORM\Index(name: 'idx_unique', columns: ['is_unique'])]
#[ORM\Index(name: 'idx_device', columns: ['device_type'])]
#[ORM\Index(name: 'idx_browser', columns: ['browser'])]
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Page::class, inversedBy: 'visits')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\Column(length: 64)]
    private ?string $visitor_fingerprint = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ip_address_hash = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $ip_country_code = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ip_country_name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $user_agent = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $referrer = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $screen_resolution = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $visited_at = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $session_id = null;

    #[ORM\Column]
    private ?bool $is_bot = false;

    #[ORM\Column]
    private ?bool $is_unique = true;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $device_type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $browser = null;

    public function __construct()
    {
        $this->visited_at = new \DateTimeImmutable();
        $this->is_bot = false;
        $this->is_unique = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getVisitorFingerprint(): ?string
    {
        return $this->visitor_fingerprint;
    }

    public function setVisitorFingerprint(string $visitor_fingerprint): static
    {
        $this->visitor_fingerprint = $visitor_fingerprint;
        return $this;
    }

    public function getIpAddressHash(): ?string
    {
        return $this->ip_address_hash;
    }

    public function setIpAddressHash(?string $ip_address_hash): static
    {
        $this->ip_address_hash = $ip_address_hash;
        return $this;
    }

    public function getIpCountryCode(): ?string
    {
        return $this->ip_country_code;
    }

    public function setIpCountryCode(?string $ip_country_code): static
    {
        $this->ip_country_code = $ip_country_code;
        return $this;
    }

    public function getIpCountryName(): ?string
    {
        return $this->ip_country_name;
    }

    public function setIpCountryName(?string $ip_country_name): static
    {
        $this->ip_country_name = $ip_country_name;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }

    public function setUserAgent(?string $user_agent): static
    {
        $this->user_agent = $user_agent;
        return $this;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function setReferrer(?string $referrer): static
    {
        $this->referrer = $referrer;
        return $this;
    }

    public function getScreenResolution(): ?string
    {
        return $this->screen_resolution;
    }

    public function setScreenResolution(?string $screen_resolution): static
    {
        $this->screen_resolution = $screen_resolution;
        return $this;
    }

    public function getVisitedAt(): ?\DateTimeImmutable
    {
        return $this->visited_at;
    }

    public function setVisitedAt(\DateTimeImmutable $visited_at): static
    {
        $this->visited_at = $visited_at;
        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function setSessionId(?string $session_id): static
    {
        $this->session_id = $session_id;
        return $this;
    }

    public function isBot(): ?bool
    {
        return $this->is_bot;
    }

    public function setIsBot(bool $is_bot): static
    {
        $this->is_bot = $is_bot;
        return $this;
    }

    public function isUnique(): ?bool
    {
        return $this->is_unique;
    }

    public function setIsUnique(bool $is_unique): static
    {
        $this->is_unique = $is_unique;
        return $this;
    }

    public function getDeviceType(): ?string
    {
        return $this->device_type;
    }

    public function setDeviceType(?string $device_type): static
    {
        $this->device_type = $device_type;
        return $this;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function setBrowser(?string $browser): static
    {
        $this->browser = $browser;
        return $this;
    }
}
