<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Code Improvement using GPT</title>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/ace-builds@1.18.0/src-min-noconflict/ace.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/ace-builds@1.18.0/css/ace.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/uikit@3.16.15/dist/js/uikit.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/uikit@3.16.15/dist/css/uikit.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;700&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jsdiff/3.5.0/diff.min.js"></script>


  <style>
    body {
      font-family: 'Open Sans', sans-serif;
    }

    .form-container {
      max-width: 800px;
      margin: 0 auto;
    }

    label {
      font-weight: bold;
      display: block;
      margin-bottom: 5px;
    }

    .uk-form-controls {
      margin-top: 10px;
    }

    #submit {
      margin-top: 10px;
    }

    #result {
      margin-top: 10px;
    }

    .code-editor {
      width: 100%;
      height: 300px;
    }
  </style>
</head>

<body>
  <div class="uk-section">
    <div class="uk-container form-container">
      <h1 class="uk-text-center">Code Improver (GPT)</h1>
      <form class="uk-form-stacked">
        <div class="uk-margin">
          <label class="uk-form-label" for="code">Enter the code:</label>
          <div class="uk-form-controls">
            <div class="code-editor" id="code"></div>
          </div>
        </div>
        <div class="uk-margin">
          <button class="uk-button uk-button-primary" id="submit">Submit</button>
        </div>

        <div class="uk-margin">
          <div id="loading-spinner" class="uk-hidden uk-flex uk-flex-center">
            <span uk-spinner></span>
          </div>
          <div id="result-container" class="uk-hidden">
            <label class="uk-form-label" for="result">Improved code:</label>
            <div class="uk-form-controls">
              <div class="code-editor" id="result"></div>
            </div>
          </div>
          <div id="error-message" class="uk-hidden">
            <p class="uk-text-danger">An error occurred while processing your code. Please try again later.</p>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
  $(document).ready(function() {
  var codeEditor = ace.edit("code");
  codeEditor.setTheme("ace/theme/monokai");
  codeEditor.getSession().setMode("ace/mode/javascript");

  var resultEditor = ace.edit("result");
  resultEditor.setTheme("ace/theme/monokai");
  resultEditor.getSession().setMode("ace/mode/javascript");
  resultEditor.setReadOnly(true);

  $("#submit").click(function() {
    resultEditor.setValue("", -1);
    $("#result-container").addClass("uk-hidden");
    $("#difference-container").remove();

    var code = codeEditor.getValue();
    var submitButton = $(this);
    submitButton.prop("disabled", true);
    $.ajax({
      type: "POST",
      url: "getcode.php",
      contentType: "application/json; charset=utf-8",
      data: JSON.stringify({
        code: code
      }),
      dataType: "json",
      success: function(data) {
        if (data.error) {
          console.log("Error:", data.error);
          resultEditor.setValue("Error: " + data.error, -1);
        } else {
          console.log("Success:", data.response);
          resultEditor.setValue(data.response, -1);
          $("#result-container").removeClass("uk-hidden");
        }
        submitButton.prop("disabled", false);

        // Move this code block inside the success callback
        var improvedCode = resultEditor.getValue();

        // Compare the contents of the two windows


        var diff = JsDiff.diffWords(code, improvedCode);

        // Create a new text area to show the differences
        var differenceContainer = document.createElement("div");
        differenceContainer.className = "uk-margin";
        differenceContainer.id = "difference-container";

        var differenceLabel = document.createElement("label");
        differenceLabel.className = "uk-form-label";
        differenceLabel.innerHTML = "Differences:";
        differenceContainer.appendChild(differenceLabel);

        var differenceEditor = document.createElement("div");
        differenceEditor.className = "uk-form-controls";

        var differenceDisplay = document.createElement("div");
        differenceDisplay.className = "code-editor";
        differenceDisplay.id = "difference";
        differenceEditor.appendChild(differenceDisplay);
        differenceContainer.appendChild(differenceEditor);

        // Add the difference display to the form
        var form = document.querySelector("form");
        form.appendChild(differenceContainer);

        // Show the differences in the difference display
        var differenceEditor = ace.edit("difference");
        differenceEditor.setTheme("ace/theme/monokai");
        differenceEditor.getSession().setMode("ace/mode/diff");
        differenceEditor.setReadOnly(true);
        differenceEditor.setValue(diff.map(function(part) {
          return part.added ? '+ ' + part.value :
            part.removed ? '- ' + part.value : '  ' + part.value;
        }).join(""), -1);
      },
      error: function(xhr, status, error) {
        console.log("Error:", xhr, status, error);
        resultEditor.setValue("Error: " + error, -1);
        submitButton.prop("disabled", false);
      }
    });
  });
});
</script>
</body>
</html>
