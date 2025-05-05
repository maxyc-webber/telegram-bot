<?php

namespace Maxyc\TelegramBot;

/**
 * Builds prompts for GigaChat based on user messages and specialists.
 *
 * @psalm-immutable
 */
class PromptBuilder
{
    private readonly ConfigProvider $config;

    /**
     * @param ConfigProvider $config Configuration provider
     */
    public function __construct(ConfigProvider $config)
    {
        $this->config = $config;
    }

    /**
     * Builds a prompt for GigaChat based on the user message and specialists.
     *
     * @param string $userMessage User message
     * @param array $specialists List of specialists
     * @return string GigaChat prompt
     * @psalm-param list<array{phone: string, name: string, services: string, description: string}> $specialists
     */
    public function build(string $userMessage, array $specialists): string
    {
        $specialistsText = array_map(
            fn($s) => "- {$s['phone']}, {$s['name']}, {$s['services']}, {$s['description']}",
            $specialists
        );
        $specialistsText = implode("\n", $specialistsText);
        return str_replace(
            ['{userMessage}', '{specialistsText}'],
            [$userMessage, $specialistsText],
            $this->config->gigaChatPromptTemplate
        );
    }
}