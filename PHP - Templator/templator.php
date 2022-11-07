<?php

/**
 * Represents the template compiler.
 */
class Templator
{
    public string $template;
    public int $if = 0;
    public int $for = 0;
    public int $foreach = 0;

    /**
     * Load a template file into memory.
     * @param string $fileName Path to the template file to be loaded.
     */

    public function loadTemplate(string $fileName)
    {
        if (!file_exists($fileName)) {
            throw new Exception("Unable to open $fileName");
            return;
        }
        $this->template = file_get_contents(
            $fileName   
        );
        // You need to implement this!

    }

    /**
     * Compile the loaded template (transpill it into interleaved-PHP) and save the result in a file.
     * @param string $fileName Path where the result should be saved.
     */
    public function compileAndSave(string $fileName)
    {
        if (is_dir($fileName)) {
            throw new Exception("Unable to open $fileName");
            return;
        }
        if (!isset($this->template)) {
            throw new Exception("Unable to open $fileName");
            return;
        }

        $this->foreach = (preg_match('/{foreach .+}/', $this->template, $matches));
        $this->for = (preg_match('/{for .+}/', $this->template, $matches));
        $this->if = (preg_match('/{if .+}/', $this->template, $matches));

        $this->foreach -= (preg_match('/{\/foreach}/', $this->template, $matches));
        $this->for -= (preg_match('/{\/for}/', $this->template, $matches));
        $this->if -= (preg_match('/{\/if}/', $this->template, $matches));
        
        if($this->foreach != 0 || $this->for != 0 || $this->if != 0) {
            throw new Exception("I am dying");
        }
        if(preg_match_all('/{foreach .+}.+{if .+}.+{\/foreach}.+{\/if}/s U x', $this->template)) {
            throw new Exception();
        }
        $this->template = preg_replace('/{= (.+)}/U', '<?= htmlspecialchars($1) ?>', $this->template);
        $this->template = preg_replace('/{if (.+)}(.+){\/if}/s U', '<?php if ($1) { ?> $2 <?php }?>', $this->template);
        $this->template = preg_replace('/{for (.+)}(.+){\/for}/s U', '<?php for ($1) { ?> $2 <?php }?>', $this->template);
        $this->template = preg_replace('/{foreach (.+)}(.+){\/foreach}/s U', '<?php foreach ($1) { ?> $2 <?php }?>', $this->template);
        $this->template = preg_replace('/{foreach (.+)}(.+){\/foreach}/s U m', '<?php foreach ($1) { ?> $2 <?php }?>', $this->template);
        
        file_put_contents(
            $fileName,
            $this->template
        );
        // You need to implement this!
    }
}