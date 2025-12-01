<?php

class Page
{
    private string $template;

    /**
     * Конструктор класса.
     * @param string $template Путь к файлу шаблона (.tpl).
     */
    public function __construct(string $template)
    {
        $this->template = $template;
    }

    /**
     * Подставляет данные в шаблон и возвращает HTML-строку.
     * @param array $data Ассоциативный массив с данными.
     * @return string HTML-код страницы.
     */
    public function Render(array $data): string
    {
        $template = file_get_contents($this->template);

        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value), $template);
        }
        return $template;
    }
}
